package com.kin.app

import android.content.Context
import com.kin.app.crypto.KinCryptoManager
import com.kin.app.trust.KinDeviceTrust
import com.kin.app.health.KinHealthCollector

/**
 * Simple validator to test Kotlin components
 * This can be called during app startup to verify everything works
 */
class Validator(private val context: Context) {

    fun validateAll(): ValidationResult {
        val results = mutableListOf<ValidationItem>()

        // Test 1: Crypto Manager
        try {
            val crypto = KinCryptoManager(context)
            val testData = "test_validation_data"
            val encrypted = crypto.encrypt(testData)
            val decrypted = crypto.decrypt(encrypted)

            if (testData == decrypted) {
                results.add(ValidationItem("Crypto Manager", true, "Encrypt/Decrypt works"))
            } else {
                results.add(ValidationItem("Crypto Manager", false, "Encrypt/Decrypt failed"))
            }
        } catch (e: Exception) {
            results.add(ValidationItem("Crypto Manager", false, e.message ?: "Unknown error"))
        }

        // Test 2: Device Trust
        try {
            val trust = KinDeviceTrust(context)
            val fingerprint = trust.getFingerprint()
            val score = trust.getTrustScore()
            val reasons = trust.getTrustReasons()

            if (fingerprint.isNotEmpty() && score in 0..100) {
                results.add(ValidationItem("Device Trust", true, "Fingerprint: ${fingerprint.take(16)}... Score: $score"))
            } else {
                results.add(ValidationItem("Device Trust", false, "Invalid fingerprint or score"))
            }
        } catch (e: Exception) {
            results.add(ValidationItem("Device Trust", false, e.message ?: "Unknown error"))
        }

        // Test 3: Health Collector
        try {
            val health = KinHealthCollector(context)
            val status = health.getHealthStatus()

            val battery = status.getInt("level")
            val charging = status.getBoolean("charging")
            val network = status.getString("network")

            if (battery in 0..100) {
                results.add(ValidationItem("Health Collector", true, "Battery: $battery%, Charging: $charging, Network: $network"))
            } else {
                results.add(ValidationItem("Health Collector", false, "Invalid battery level: $battery"))
            }
        } catch (e: Exception) {
            results.add(ValidationItem("Health Collector", false, e.message ?: "Unknown error"))
        }

        val allPassed = results.all { it.passed }
        return ValidationResult(allPassed, results)
    }

    data class ValidationItem(val name: String, val passed: Boolean, val message: String)
    data class ValidationResult(val allPassed: Boolean, val results: List<ValidationItem>)
}
