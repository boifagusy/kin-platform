package com.kin.plugins.network

import android.Manifest
import android.content.Context
import android.net.ConnectivityManager
import android.net.Network
import android.net.NetworkCapabilities
import android.net.wifi.WifiManager
import android.os.Build
import android.telephony.TelephonyManager
import androidx.core.app.NotificationCompat
import com.getcapacitor.JSObject
import com.getcapacitor.Plugin
import com.getcapacitor.PluginCall
import com.getcapacitor.PluginMethod
import com.getcapacitor.annotation.CapacitorPlugin
import com.getcapacitor.annotation.Permission
import java.text.SimpleDateFormat
import java.util.Date
import java.util.Locale

@CapacitorPlugin(
    name = "KinNetwork",
    permissions = [
        Permission(
            strings = [Manifest.permission.ACCESS_NETWORK_STATE],
            alias = "network"
        ),
        Permission(
            strings = [Manifest.permission.ACCESS_WIFI_STATE],
            alias = "wifi"
        )
    ]
)
class KinNetworkPlugin : Plugin() {
    private lateinit var connectivityManager: ConnectivityManager
    private lateinit var wifiManager: WifiManager
    private lateinit var telephonyManager: TelephonyManager
    private var networkCallback: ConnectivityManager.NetworkCallback? = null
    private var isMonitoring = false

    override fun load() {
        connectivityManager = getContext().getSystemService(Context.CONNECTIVITY_SERVICE) as ConnectivityManager
        wifiManager = getContext().applicationContext.getSystemService(Context.WIFI_SERVICE) as WifiManager
        telephonyManager = getContext().getSystemService(Context.TELEPHONY_SERVICE) as TelephonyManager
    }

    @PluginMethod
    fun getNetworkInfo(call: PluginCall) {
        try {
            val info = JSObject().apply {
                put("connected", isConnected())
                put("type", getNetworkType())
                put("carrier", getCarrier())
                put("signalStrength", getSignalStrength())
                put("isMetered", isMetered())
                put("ipAddress", getIPAddress())
            }
            call.resolve(info)
        } catch (e: Exception) {
            call.reject("Failed to get network info: ${e.message}")
        }
    }

    @PluginMethod
    fun isConnected(call: PluginCall) {
        call.resolve(JSObject().apply {
            put("connected", isConnected())
        })
    }

    @PluginMethod
    fun getNetworkType(call: PluginCall) {
        call.resolve(JSObject().apply {
            put("type", getNetworkType())
        })
    }

    @PluginMethod
    fun getCarrier(call: PluginCall) {
        call.resolve(JSObject().apply {
            put("carrier", getCarrier())
        })
    }

    @PluginMethod
    fun getIPAddress(call: PluginCall) {
        call.resolve(JSObject().apply {
            put("ipAddress", getIPAddress())
        })
    }

    @PluginMethod
    fun getSignalStrength(call: PluginCall) {
        call.resolve(JSObject().apply {
            put("strength", getSignalStrength())
        })
    }

    @PluginMethod
    fun isMetered(call: PluginCall) {
        call.resolve(JSObject().apply {
            put("metered", isMetered())
        })
    }

    @PluginMethod
    fun startMonitoring(call: PluginCall) {
        if (isMonitoring) {
            call.resolve(JSObject().apply { put("success", true) })
            return
        }

        registerNetworkCallback()
        isMonitoring = true
        call.resolve(JSObject().apply { put("success", true) })
    }

    @PluginMethod
    fun stopMonitoring(call: PluginCall) {
        if (networkCallback != null) {
            connectivityManager.unregisterNetworkCallback(networkCallback!!)
            networkCallback = null
        }
        isMonitoring = false
        call.resolve(JSObject().apply { put("success", true) })
    }

    private fun isConnected(): Boolean {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
            val network = connectivityManager.activeNetwork
            val capabilities = connectivityManager.getNetworkCapabilities(network)
            return capabilities != null && capabilities.hasCapability(NetworkCapabilities.NET_CAPABILITY_INTERNET)
        }
        return false
    }

    private fun getNetworkType(): String {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
            val network = connectivityManager.activeNetwork
            val capabilities = connectivityManager.getNetworkCapabilities(network) ?: return "none"
            
            return when {
                capabilities.hasTransport(NetworkCapabilities.TRANSPORT_WIFI) -> "wifi"
                capabilities.hasTransport(NetworkCapabilities.TRANSPORT_CELLULAR) -> "cellular"
                capabilities.hasTransport(NetworkCapabilities.TRANSPORT_ETHERNET) -> "ethernet"
                capabilities.hasTransport(NetworkCapabilities.TRANSPORT_BLUETOOTH) -> "bluetooth"
                capabilities.hasTransport(NetworkCapabilities.TRANSPORT_VPN) -> "vpn"
                else -> "unknown"
            }
        }
        return "unknown"
    }

    private fun getCarrier(): String? {
        return if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.P) {
            telephonyManager.simCarrierIdName
        } else {
            telephonyManager.networkOperatorName
        }
    }

    private fun getSignalStrength(): Int? {
        return if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.Q) {
            telephonyManager.signalStrength?.level
        } else {
            null
        }
    }

    private fun isMetered(): Boolean {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
            val network = connectivityManager.activeNetwork
            val capabilities = connectivityManager.getNetworkCapabilities(network)
            return capabilities?.hasCapability(NetworkCapabilities.NET_CAPABILITY_NOT_METERED) == false
        }
        return false
    }

    private fun getIPAddress(): String? {
        val wifiInfo = wifiManager.connectionInfo
        val ip = wifiInfo?.ipAddress ?: return null
        return String.format("%d.%d.%d.%d", 
            ip and 0xff, 
            (ip shr 8) and 0xff, 
            (ip shr 16) and 0xff, 
            (ip shr 24) and 0xff
        )
    }

    private fun registerNetworkCallback() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.N) {
            networkCallback = object : ConnectivityManager.NetworkCallback() {
                override fun onAvailable(network: Network) {
                    notifyConnectionChange(true)
                }

                override fun onLost(network: Network) {
                    notifyConnectionChange(false)
                }

                override fun onCapabilitiesChanged(network: Network, networkCapabilities: NetworkCapabilities) {
                    val event = JSObject().apply {
                        put("type", getNetworkType())
                        put("isMetered", isMetered())
                        put("timestamp", getCurrentTimestamp())
                    }
                    notifyListeners("networkChanged", event)
                }
            }
            connectivityManager.registerDefaultNetworkCallback(networkCallback!!)
        }
    }

    private fun notifyConnectionChange(connected: Boolean) {
        val event = JSObject().apply {
            put("connected", connected)
            put("type", if (connected) getNetworkType() else "none")
            put("timestamp", getCurrentTimestamp())
        }
        notifyListeners("connectionChange", event)
    }

    private fun getCurrentTimestamp(): String {
        return SimpleDateFormat("yyyy-MM-dd'T'HH:mm:ss.SSS'Z'", Locale.US).format(Date())
    }
}
