package com.kin.plugins.storage

import android.content.Context
import android.content.SharedPreferences
import android.security.keystore.KeyGenParameterSpec
import android.security.keystore.KeyProperties
import android.util.Base64
import com.getcapacitor.JSObject
import com.getcapacitor.Plugin
import com.getcapacitor.PluginCall
import com.getcapacitor.PluginMethod
import com.getcapacitor.annotation.CapacitorPlugin
import com.google.gson.Gson
import com.google.gson.reflect.TypeToken
import java.security.KeyStore
import javax.crypto.Cipher
import javax.crypto.KeyGenerator
import javax.crypto.SecretKey
import javax.crypto.spec.GCMParameterSpec

@CapacitorPlugin(name = "KinStorage")
class KinStoragePlugin : Plugin() {
    private lateinit var preferences: SharedPreferences
    private lateinit var encryptedStorage: EncryptedStorage
    private val gson = Gson()
    private val MAX_ITEM_SIZE = 1024 * 1024 // 1MB
    private val MAX_TOTAL_SIZE = 50 * 1024 * 1024 // 50MB

    override fun load() {
        preferences = getContext().getSharedPreferences("kin_storage", Context.MODE_PRIVATE)
        encryptedStorage = EncryptedStorage(getContext())
    }

    @PluginMethod
    fun save(call: PluginCall) {
        val key = call.getString("key") ?: run {
            call.reject("Key is required")
            return
        }
        val value = call.getString("value") ?: run {
            call.reject("Value is required")
            return
        }
        val expiresIn = call.getInt("expiresIn", -1)

        try {
            // Check size limits
            if (value.length > MAX_ITEM_SIZE) {
                call.reject("Value exceeds max size: ${MAX_ITEM_SIZE} bytes")
                return
            }

            val currentSize = getCurrentSize()
            if (currentSize + value.length > MAX_TOTAL_SIZE) {
                // Clear expired items first
                clearExpired()
                // Check again
                if (getCurrentSize() + value.length > MAX_TOTAL_SIZE) {
                    call.reject("Storage quota exceeded (${MAX_TOTAL_SIZE / (1024 * 1024)}MB)")
                    return
                }
            }

            val item = StorageItem(
                value = encryptedStorage.encrypt(value),
                timestamp = System.currentTimeMillis(),
                expiresIn = expiresIn
            )
            val json = gson.toJson(item)
            preferences.edit().putString(key, json).apply()
            call.resolve(JSObject().apply { put("success", true) })
        } catch (e: Exception) {
            call.reject("Failed to save: ${e.message}")
        }
    }

    @PluginMethod
    fun load(call: PluginCall) {
        val key = call.getString("key") ?: run {
            call.reject("Key is required")
            return
        }

        try {
            val json = preferences.getString(key, null)
            if (json == null) {
                call.resolve(JSObject().apply { put("value", null) })
                return
            }

            val item = gson.fromJson(json, StorageItem::class.java)
            
            // Check expiry
            if (item.expiresIn > 0 && System.currentTimeMillis() > item.timestamp + (item.expiresIn * 1000L)) {
                preferences.edit().remove(key).apply()
                call.resolve(JSObject().apply { put("value", null) })
                return
            }

            val decrypted = encryptedStorage.decrypt(item.value)
            call.resolve(JSObject().apply { put("value", decrypted) })
        } catch (e: Exception) {
            call.reject("Failed to load: ${e.message}")
        }
    }

    @PluginMethod
    fun delete(call: PluginCall) {
        val key = call.getString("key") ?: run {
            call.reject("Key is required")
            return
        }
        preferences.edit().remove(key).apply()
        call.resolve(JSObject().apply { put("success", true) })
    }

    @PluginMethod
    fun clear(call: PluginCall) {
        preferences.edit().clear().apply()
        call.resolve(JSObject().apply { put("success", true) })
    }

    @PluginMethod
    fun getAllKeys(call: PluginCall) {
        val keys = preferences.all.keys.toList()
        call.resolve(JSObject().apply { put("keys", keys) })
    }

    @PluginMethod
    fun getStats(call: PluginCall) {
        val all = preferences.all
        var totalSize = 0L
        var itemCount = 0
        
        for ((_, value) in all) {
            val str = value as? String ?: continue
            totalSize += str.length * 2L
            itemCount++
        }

        call.resolve(JSObject().apply {
            put("totalItems", itemCount)
            put("totalSize", totalSize)
            put("keys", all.keys.toList())
            put("quota", MAX_TOTAL_SIZE)
            put("available", MAX_TOTAL_SIZE - totalSize)
        })
    }

    @PluginMethod
    fun has(call: PluginCall) {
        val key = call.getString("key") ?: run {
            call.reject("Key is required")
            return
        }
        val exists = preferences.contains(key)
        call.resolve(JSObject().apply { put("exists", exists) })
    }

    @PluginMethod
    fun clearExpired(call: PluginCall) {
        val cleared = clearExpired()
        call.resolve(JSObject().apply { put("cleared", cleared) })
    }

    @PluginMethod
    fun exportData(call: PluginCall) {
        val all = preferences.all
        val data = mutableMapOf<String, String>()
        for ((key, value) in all) {
            data[key] = value.toString()
        }
        val json = gson.toJson(data)
        call.resolve(JSObject().apply { put("data", json) })
    }

    @PluginMethod
    fun importData(call: PluginCall) {
        val data = call.getString("data") ?: run {
            call.reject("Data is required")
            return
        }

        try {
            val type = object : TypeToken<Map<String, String>>() {}.type
            val parsed: Map<String, String> = gson.fromJson(data, type)
            for ((key, value) in parsed) {
                preferences.edit().putString(key, value).apply()
            }
            call.resolve(JSObject().apply { put("success", true) })
        } catch (e: Exception) {
            call.reject("Failed to import: ${e.message}")
        }
    }

    private fun getCurrentSize(): Long {
        var size = 0L
        for ((_, value) in preferences.all) {
            val str = value as? String ?: continue
            size += str.length * 2L
        }
        return size
    }

    private fun clearExpired(): Int {
        var cleared = 0
        val all = preferences.all
        for ((key, value) in all) {
            try {
                val item = gson.fromJson(value.toString(), StorageItem::class.java)
                if (item.expiresIn > 0 && System.currentTimeMillis() > item.timestamp + (item.expiresIn * 1000L)) {
                    preferences.edit().remove(key).apply()
                    cleared++
                }
            } catch (e: Exception) {
                // Invalid item, skip
            }
        }
        return cleared
    }

    data class StorageItem(
        val value: String,
        val timestamp: Long,
        val expiresIn: Int // -1 = never expires
    )
}

// Encrypted Storage Helper
class EncryptedStorage(private val context: Context) {
    private val keyStore = KeyStore.getInstance("AndroidKeyStore")
    private val KEY_ALIAS = "KinStorageKey"
    private val TRANSFORMATION = "AES/GCM/NoPadding"
    private val IV_SIZE = 12

    init {
        keyStore.load(null)
        if (!keyStore.containsAlias(KEY_ALIAS)) {
            generateKey()
        }
    }

    fun encrypt(data: String): String {
        val secretKey = keyStore.getKey(KEY_ALIAS, null) as SecretKey
        val cipher = Cipher.getInstance(TRANSFORMATION)
        cipher.init(Cipher.ENCRYPT_MODE, secretKey)
        
        val encrypted = cipher.doFinal(data.toByteArray(Charsets.UTF_8))
        val iv = cipher.iv
        
        val combined = ByteArray(iv.size + encrypted.size)
        System.arraycopy(iv, 0, combined, 0, iv.size)
        System.arraycopy(encrypted, 0, combined, iv.size, encrypted.size)
        
        return Base64.encodeToString(combined, Base64.DEFAULT)
    }

    fun decrypt(encrypted: String): String {
        val combined = Base64.decode(encrypted, Base64.DEFAULT)
        val iv = combined.sliceArray(0 until IV_SIZE)
        val data = combined.sliceArray(IV_SIZE until combined.size)
        
        val secretKey = keyStore.getKey(KEY_ALIAS, null) as SecretKey
        val cipher = Cipher.getInstance(TRANSFORMATION)
        cipher.init(Cipher.DECRYPT_MODE, secretKey, GCMParameterSpec(128, iv))
        
        val decrypted = cipher.doFinal(data)
        return String(decrypted, Charsets.UTF_8)
    }

    private fun generateKey() {
        val keyGenerator = KeyGenerator.getInstance(
            KeyProperties.KEY_ALGORITHM_AES,
            "AndroidKeyStore"
        )
        val spec = KeyGenParameterSpec.Builder(
            KEY_ALIAS,
            KeyProperties.PURPOSE_ENCRYPT or KeyProperties.PURPOSE_DECRYPT
        )
        .setBlockModes(KeyProperties.BLOCK_MODE_GCM)
        .setEncryptionPaddings(KeyProperties.ENCRYPTION_PADDING_NONE)
        .setKeySize(256)
        .build()
        keyGenerator.init(spec)
        keyGenerator.generateKey()
    }
}
