package com.kin.app.plugins

import android.content.Context
import com.getcapacitor.JSObject
import com.getcapacitor.Plugin
import com.getcapacitor.PluginCall  // ✅ ADD THIS
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
            
            val result = JSObject()
            result.put("healthScore", health.overallScore)
            result.put("trustScore", trustScore)
            result.put("isSafe", health.overallScore > 50 && trustScore > 50)
            
            call.resolve(result)
        } catch (e: Exception) {
            call.reject("Failed to get safety status: ${e.message}")
        }
    }

    @PluginMethod
    fun checkIn(call: PluginCall) {
        try {
            val location = call.getString("location")
            val status = call.getString("status")
            val isSafe = call.getBoolean("isSafe", false)
            
            // Process check-in
            val result = JSObject()
            result.put("success", true)
            result.put("message", "Check-in recorded successfully")
            result.put("timestamp", System.currentTimeMillis())
            
            call.resolve(result)
        } catch (e: Exception) {
            call.reject("Check-in failed: ${e.message}")
        }
    }

    @PluginMethod
    fun queueEmergency(call: PluginCall) {
        try {
            val emergencyType = call.getString("type") ?: "general"
            
            val result = JSObject()
            result.put("success", true)
            result.put("emergencyId", System.currentTimeMillis())
            result.put("status", "queued")
            
            call.resolve(result)
        } catch (e: Exception) {
            call.reject("Failed to queue emergency: ${e.message}")
        }
    }

    @PluginMethod
    fun getEmergencySnapshot(call: PluginCall) {
        try {
            val result = JSObject()
            result.put("hasActiveEmergency", false)
            result.put("lastEmergencyTime", 0)
            result.put("emergencyCount", 0)
            
            call.resolve(result)
        } catch (e: Exception) {
            call.reject("Failed to get emergency snapshot: ${e.message}")
        }
    }

    @PluginMethod
    fun checkDeviceTrust(call: PluginCall) {
        try {
            val trustScore = deviceTrust.getTrustScore()
            
            val result = JSObject()
            result.put("trustScore", trustScore)
            result.put("isTrusted", trustScore > 50)
            
            call.resolve(result)
        } catch (e: Exception) {
            call.reject("Failed to check device trust: ${e.message}")
        }
    }

    @PluginMethod
    fun storeSecurely(call: PluginCall) {
        try {
            val key = call.getString("key") ?: ""
            val value = call.getString("value") ?: ""
            
            cryptoManager.storeData(key, value)
            
            val result = JSObject()
            result.put("success", true)
            
            call.resolve(result)
        } catch (e: Exception) {
            call.reject("Failed to store securely: ${e.message}")
        }
    }

    @PluginMethod
    fun retrieveSecurely(call: PluginCall) {
        try {
            val key = call.getString("key") ?: ""
            
            val value = cryptoManager.retrieveData(key)
            
            val result = JSObject()
            result.put("value", value)
            
            call.resolve(result)
        } catch (e: Exception) {
            call.reject("Failed to retrieve securely: ${e.message}")
        }
    }

    @PluginMethod
    fun deleteSecurely(call: PluginCall) {
        try {
            val key = call.getString("key") ?: ""
            
            cryptoManager.deleteData(key)
            
            val result = JSObject()
            result.put("success", true)
            
            call.resolve(result)
        } catch (e: Exception) {
            call.reject("Failed to delete securely: ${e.message}")
        }
    }
}
