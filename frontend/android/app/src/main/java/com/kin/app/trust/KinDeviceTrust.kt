package com.kin.app.trust

import android.content.Context
import android.os.Build
import android.provider.Settings
import android.telephony.TelephonyManager
import java.security.MessageDigest

class KinDeviceTrust(private val context: Context) {

    private val cachedFingerprint: String = generateFingerprint()
    private val cachedTrustScore: Int = calculateTrustScore()

    fun getFingerprint(): String = cachedFingerprint

    fun getTrustScore(): Int = cachedTrustScore

    fun getTrustReasons(): Array<String> {
        val reasons = mutableListOf<String>()

        if (isDeviceRooted()) reasons.add("Device is rooted")
        if (isEmulator()) reasons.add("Device is emulator")
        if (isAppReinstalled()) reasons.add("App was reinstalled")
        if (isSIMChanged()) reasons.add("SIM was changed")

        return reasons.toTypedArray()
    }

    private fun generateFingerprint(): String {
        return try {
            val sb = StringBuilder()

            // Android ID
            val androidId = Settings.Secure.getString(
                context.contentResolver,
                Settings.Secure.ANDROID_ID
            )
            sb.append(androidId)

            // Build info
            sb.append(Build.MANUFACTURER)
            sb.append(Build.MODEL)
            sb.append(Build.DEVICE)
            sb.append(Build.PRODUCT)
            sb.append(Build.VERSION.SDK_INT)

            // Hash it
            val digest = MessageDigest.getInstance("SHA-256")
            val hash = digest.digest(sb.toString().toByteArray())

            hash.joinToString("") { String.format("%02x", it) }
        } catch (e: Exception) {
            "FINGERPRINT_ERROR"
        }
    }

    private fun calculateTrustScore(): Int {
        var score = 100

        if (isDeviceRooted()) score -= 30
        if (isEmulator()) score -= 50
        if (isAppReinstalled()) score -= 20
        if (isSIMChanged()) score -= 15

        return score.coerceIn(0, 100)
    }

    private fun isDeviceRooted(): Boolean {
        // Check for known root paths
        val paths = listOf(
            "/system/app/Superuser.apk",
            "/sbin/su",
            "/system/bin/su",
            "/system/xbin/su",
            "/data/local/xbin/su",
            "/data/local/bin/su",
            "/system/sd/xbin/su",
            "/system/bin/failsafe/su",
            "/data/local/su"
        )

        for (path in paths) {
            try {
                if (java.io.File(path).exists()) return true
            } catch (_: Exception) { /* ignore */ }
        }

        // Check for su command
        return try {
            val process = Runtime.getRuntime().exec("which su")
            process.waitFor() == 0
        } catch (_: Exception) {
            false
        }
    }

    private fun isEmulator(): Boolean {
        return Build.FINGERPRINT.startsWith("generic") ||
                Build.FINGERPRINT.startsWith("unknown") ||
                Build.MODEL.contains("google_sdk") ||
                Build.MODEL.contains("Emulator") ||
                Build.MODEL.contains("Android SDK built for x86") ||
                Build.MANUFACTURER.contains("Genymotion") ||
                (Build.BRAND.startsWith("generic") && Build.DEVICE.startsWith("generic")) ||
                Build.PRODUCT == "google_sdk"
    }

    private fun isAppReinstalled(): Boolean {
        return try {
            val packageInfo = context.packageManager.getPackageInfo(context.packageName, 0)
            packageInfo.firstInstallTime == packageInfo.lastUpdateTime
        } catch (_: Exception) {
            false
        }
    }

    private fun isSIMChanged(): Boolean {
        return try {
            val tm = context.getSystemService(Context.TELEPHONY_SERVICE) as? TelephonyManager
            val simSerial = tm?.simSerialNumber

            if (simSerial != null) {
                val prefs = context.getSharedPreferences("KinDevicePrefs", Context.MODE_PRIVATE)
                val storedSim = prefs.getString("sim_serial", null)

                if (storedSim == null) {
                    // First time, store it
                    prefs.edit().putString("sim_serial", simSerial).apply()
                    return false
                }
                return storedSim != simSerial
            }
            false
        } catch (_: Exception) {
            false
        }
    }
}
