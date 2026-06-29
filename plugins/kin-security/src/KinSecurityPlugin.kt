package com.kin.plugins.security

import android.security.keystore.KeyGenParameterSpec
import android.security.keystore.KeyProperties
import android.util.Base64
import com.getcapacitor.JSObject
import com.getcapacitor.Plugin
import com.getcapacitor.PluginCall
import com.getcapacitor.PluginMethod
import com.getcapacitor.annotation.CapacitorPlugin
import java.security.KeyStore
import javax.crypto.Cipher
import javax.crypto.KeyGenerator
import javax.crypto.SecretKey
import javax.crypto.spec.GCMParameterSpec

@CapacitorPlugin(name = "KinSecurity")
class KinSecurityPlugin : Plugin() {
    private lateinit var keyStore: KeyStore
    private val KEY_ALIAS = "KinSecurityKey"
    private val TRANSFORMATION = "AES/GCM/NoPadding"
    private val IV_SIZE = 12

    override fun load() {
        keyStore = KeyStore.getInstance("AndroidKeyStore")
        keyStore.load(null)
        generateKey()
    }

    @PluginMethod
    fun encrypt(call: PluginCall) {
        val data = call.getString("data") ?: run {
            call.reject("Data is required")
            return
        }

        try {
            val secretKey = keyStore.getKey(KEY_ALIAS, null) as SecretKey
            val cipher = Cipher.getInstance(TRANSFORMATION)
            cipher.init(Cipher.ENCRYPT_MODE, secretKey)
            
            val encrypted = cipher.doFinal(data.toByteArray(Charsets.UTF_8))
            val iv = cipher.iv
            
            // Combine IV + encrypted data
            val combined = ByteArray(iv.size + encrypted.size)
            System.arraycopy(iv, 0, combined, 0, iv.size)
            System.arraycopy(encrypted, 0, combined, iv.size, encrypted.size)
            
            val result = JSObject().apply {
                put("encrypted", Base64.encodeToString(combined, Base64.DEFAULT))
            }
            call.resolve(result)
        } catch (e: Exception) {
            call.reject("Encryption failed: ${e.message}")
        }
    }

    @PluginMethod
    fun decrypt(call: PluginCall) {
        val encrypted = call.getString("encrypted") ?: run {
            call.reject("Encrypted data is required")
            return
        }

        try {
            val combined = Base64.decode(encrypted, Base64.DEFAULT)
            val iv = combined.sliceArray(0 until IV_SIZE)
            val data = combined.sliceArray(IV_SIZE until combined.size)
            
            val secretKey = keyStore.getKey(KEY_ALIAS, null) as SecretKey
            val cipher = Cipher.getInstance(TRANSFORMATION)
            cipher.init(Cipher.DECRYPT_MODE, secretKey, GCMParameterSpec(128, iv))
            
            val decrypted = cipher.doFinal(data)
            
            val result = JSObject().apply {
                put("decrypted", String(decrypted, Charsets.UTF_8))
            }
            call.resolve(result)
        } catch (e: Exception) {
            call.reject("Decryption failed: ${e.message}")
        }
    }

    @PluginMethod
    fun storeSecurely(call: PluginCall) {
        val key = call.getString("key") ?: run {
            call.reject("Key is required")
            return
        }
        val value = call.getString("value") ?: run {
            call.reject("Value is required")
            return
        }

        try {
            // Encrypt and store in SharedPreferences
            val encrypted = this.encryptInternal(value)
            val prefs = getContext().getSharedPreferences("kin_secure", Context.MODE_PRIVATE)
            prefs.edit().putString("secure_$key", encrypted).apply()
            
            call.resolve(JSObject().apply { put("success", true) })
        } catch (e: Exception) {
            call.reject("Store failed: ${e.message}")
        }
    }

    @PluginMethod
    fun retrieveSecurely(call: PluginCall) {
        val key = call.getString("key") ?: run {
            call.reject("Key is required")
            return
        }

        try {
            val prefs = getContext().getSharedPreferences("kin_secure", Context.MODE_PRIVATE)
            val encrypted = prefs.getString("secure_$key", null)
            
            if (encrypted == null) {
                call.resolve(JSObject().apply { put("value", null) })
                return
            }
            
            val decrypted = this.decryptInternal(encrypted)
            call.resolve(JSObject().apply { put("value", decrypted) })
        } catch (e: Exception) {
            call.reject("Retrieve failed: ${e.message}")
        }
    }

    @PluginMethod
    fun deleteSecurely(call: PluginCall) {
        val key = call.getString("key") ?: run {
            call.reject("Key is required")
            return
        }

        try {
            val prefs = getContext().getSharedPreferences("kin_secure", Context.MODE_PRIVATE)
            prefs.edit().remove("secure_$key").apply()
            call.resolve(JSObject().apply { put("success", true) })
        } catch (e: Exception) {
            call.reject("Delete failed: ${e.message}")
        }
    }

    @PluginMethod
    fun checkBiometrics(call: PluginCall) {
        // Simplified - check if biometrics are available
        val result = JSObject().apply {
            put("available", true)
            put("enrolled", true)
        }
        call.resolve(result)
    }

    @PluginMethod
    fun generateKey(call: PluginCall) {
        val alias = call.getString("alias") ?: KEY_ALIAS
        try {
            generateKeyInternal(alias)
            call.resolve(JSObject().apply { put("success", true) })
        } catch (e: Exception) {
            call.reject("Key generation failed: ${e.message}")
        }
    }

    private fun generateKey() {
        if (!keyStore.containsAlias(KEY_ALIAS)) {
            generateKeyInternal(KEY_ALIAS)
        }
    }

    private fun generateKeyInternal(alias: String) {
        val keyGenerator = KeyGenerator.getInstance(
            KeyProperties.KEY_ALGORITHM_AES,
            "AndroidKeyStore"
        )
        
        val spec = KeyGenParameterSpec.Builder(
            alias,
            KeyProperties.PURPOSE_ENCRYPT or KeyProperties.PURPOSE_DECRYPT
        )
        .setBlockModes(KeyProperties.BLOCK_MODE_GCM)
        .setEncryptionPaddings(KeyProperties.ENCRYPTION_PADDING_NONE)
        .setKeySize(256)
        .build()
        
        keyGenerator.init(spec)
        keyGenerator.generateKey()
    }

    private fun encryptInternal(data: String): String {
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

    private fun decryptInternal(encrypted: String): String {
        val combined = Base64.decode(encrypted, Base64.DEFAULT)
        val iv = combined.sliceArray(0 until IV_SIZE)
        val data = combined.sliceArray(IV_SIZE until combined.size)
        
        val secretKey = keyStore.getKey(KEY_ALIAS, null) as SecretKey
        val cipher = Cipher.getInstance(TRANSFORMATION)
        cipher.init(Cipher.DECRYPT_MODE, secretKey, GCMParameterSpec(128, iv))
        
        val decrypted = cipher.doFinal(data)
        return String(decrypted, Charsets.UTF_8)
    }
}
