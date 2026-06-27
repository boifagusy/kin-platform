package com.kin.app.plugins

import android.content.Context
import com.getcapacitor.JSObject
import com.getcapacitor.Plugin
import com.getcapacitor.PluginMethod
import com.getcapacitor.annotation.CapacitorPlugin
import com.kin.app.crypto.KinCryptoManager
import com.kin.app.trust.KinDeviceTrust
import com.kin.app.health.KinHealthCollector

@CapacitorPlugin(name = "KinSafety")
class KinSafetyPlugin : Plugin() {

    private lateinit var cryptoManager: KinCryptoManager
    private lateinit var deviceTrust: KinDeviceTrust
    private lateinit var healthCollector: KinHealthCollector

    override fun load() {
        val context = context ?: return
        cryptoManager = KinCryptoManager(context)
        deviceTrust = KinDeviceTrust(context)
        healthCollector = KinHealthCollector(context)
    }

    @PluginMethod
    fun getSafetyStatus(call: PluginCall) {
        try {
            val health = healthCollector.getHealthStatus()
            val trustScore = deviceTrust.getTrustScore()
            val fingerprint = deviceTrust.getFingerprint()

            val result = JSObject().apply {
                put("confidence", 85)
                put("deviceTrust", trustScore)
                put("fingerprint", fingerprint)
                put("battery", health.getInt("level"))
                put("charging", health.getBoolean("charging"))
                put("network", health.getString("network"))
                put("timestamp", System.currentTimeMillis())
            }
            call.resolve(result)
        } catch (e: Exception) {
            call.reject("Failed to get safety status: ${e.message}")
        }
    }

    @PluginMethod
    fun performCheckIn(call: PluginCall) {
        try {
            val pin = call.getString("pin")
            val duressPin = call.getString("duressPin")
            var isDuress = call.getBoolean("isDuress", false)

            // Check duress pin
            if (duressPin != null && cryptoManager.verifyDuressPin(duressPin)) {
                isDuress = true
                triggerSilentSOS()
            }

            val result = JSObject().apply {
                put("success", true)
                put("confidence", if (isDuress) 20 else 85)
                put("isDuress", isDuress)
                put("timestamp", System.currentTimeMillis())
            }
            call.resolve(result)
        } catch (e: Exception) {
            call.reject("Check-in failed: ${e.message}")
        }
    }

    @PluginMethod
    fun queueEmergency(call: PluginCall) {
        try {
            val type = call.getString("type", "sos")

            val result = JSObject().apply {
                put("queued", true)
                put("id", "emergency_${System.currentTimeMillis()}")
                put("priority", "critical")
                put("timestamp", System.currentTimeMillis())
            }
            call.resolve(result)
        } catch (e: Exception) {
            call.reject("Failed to queue emergency: ${e.message}")
        }
    }

    @PluginMethod
    fun getEmergencySnapshot(call: PluginCall) {
        try {
            val health = healthCollector.getHealthStatus()
            val trustScore = deviceTrust.getTrustScore()
            val fingerprint = deviceTrust.getFingerprint()

            val result = JSObject().apply {
                put("location", getLastKnownLocation())
                put("battery", health)
                put("trustScore", trustScore)
                put("fingerprint", fingerprint)
                put("timestamp", System.currentTimeMillis())
            }
            call.resolve(result)
        } catch (e: Exception) {
            call.reject("Failed to get emergency snapshot: ${e.message}")
        }
    }

    @PluginMethod
    fun isDeviceTrusted(call: PluginCall) {
        try {
            val score = deviceTrust.getTrustScore()
            val reasons = deviceTrust.getTrustReasons()

            val result = JSObject().apply {
                put("trusted", score >= 70)
                put("score", score)
                put("reasons", reasons)
                put("timestamp", System.currentTimeMillis())
            }
            call.resolve(result)
        } catch (e: Exception) {
            call.reject("Failed to check device trust: ${e.message}")
        }
    }

    @PluginMethod
    fun storeSecure(call: PluginCall) {
        try {
            val key = call.getString("key") ?: throw IllegalArgumentException("Key is required")
            val value = call.getString("value") ?: throw IllegalArgumentException("Value is required")

            cryptoManager.storeSecure(key, value)
            call.resolve(JSObject().apply { put("success", true) })
        } catch (e: Exception) {
            call.reject("Failed to store securely: ${e.message}")
        }
    }

    @PluginMethod
    fun retrieveSecure(call: PluginCall) {
        try {
            val key = call.getString("key") ?: throw IllegalArgumentException("Key is required")

            val value = cryptoManager.retrieveSecure(key)
            call.resolve(JSObject().apply {
                put("success", true)
                put("value", value)
            })
        } catch (e: Exception) {
            call.reject("Failed to retrieve securely: ${e.message}")
        }
    }

    @PluginMethod
    fun deleteSecure(call: PluginCall) {
        try {
            val key = call.getString("key") ?: throw IllegalArgumentException("Key is required")

            cryptoManager.deleteSecure(key)
            call.resolve(JSObject().apply { put("success", true) })
        } catch (e: Exception) {
            call.reject("Failed to delete securely: ${e.message}")
        }
    }

    private fun triggerSilentSOS() {
        // TODO: Connect to existing SOS flow
    }

    private fun getLastKnownLocation(): JSObject {
        return JSObject().apply {
            put("lat", 0.0)
            put("lng", 0.0)
            put("accuracy", 0)
        }
    }
}
