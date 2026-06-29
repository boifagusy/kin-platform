package com.kin.plugins.heartbeat

import android.app.NotificationChannel
import android.app.NotificationManager
import android.app.Service
import android.content.Context
import android.content.Intent
import android.os.Build
import android.os.IBinder
import androidx.core.app.NotificationCompat
import com.getcapacitor.JSObject
import com.getcapacitor.Plugin
import com.getcapacitor.PluginCall
import com.getcapacitor.PluginMethod
import com.getcapacitor.annotation.CapacitorPlugin
import kotlinx.coroutines.*

@CapacitorPlugin(name = "KinHeartbeat")
class KinHeartbeatPlugin : Plugin() {
    private var isRunning = false
    private var job: Job? = null
    private val scope = CoroutineScope(Dispatchers.IO + SupervisorJob())
    private var currentStatus = "idle"
    private var lastHeartbeat: String? = null
    private var interval = 60000L

    @PluginMethod
    fun start(call: PluginCall) {
        val intervalMs = call.getLong("interval", 60000L)
        this.interval = intervalMs

        if (isRunning) {
            call.resolve(JSObject().apply { put("success", true) })
            return
        }

        isRunning = true
        currentStatus = "active"

        job = scope.launch {
            while (isRunning) {
                sendHeartbeat()
                delay(intervalMs)
            }
        }

        call.resolve(JSObject().apply { put("success", true) })
    }

    @PluginMethod
    fun stop(call: PluginCall) {
        isRunning = false
        job?.cancel()
        job = null
        currentStatus = "idle"
        call.resolve(JSObject().apply { put("success", true) })
    }

    @PluginMethod
    fun getStatus(call: PluginCall) {
        val result = JSObject().apply {
            put("status", currentStatus)
            put("lastHeartbeat", lastHeartbeat)
            put("interval", interval)
            put("isRunning", isRunning)
        }
        call.resolve(result)
    }

    @PluginMethod
    fun getLastHeartbeat(call: PluginCall) {
        if (lastHeartbeat == null) {
            call.resolve(null)
        } else {
            val result = JSObject().apply {
                put("timestamp", lastHeartbeat)
                put("status", currentStatus)
            }
            call.resolve(result)
        }
    }

    @PluginMethod
    fun setStatus(call: PluginCall) {
        val status = call.getString("status") ?: run {
            call.reject("Status is required")
            return
        }
        currentStatus = status
        call.resolve(JSObject().apply { put("success", true) })
    }

    private suspend fun sendHeartbeat() {
        lastHeartbeat = System.currentTimeMillis().toString()
        
        // Collect data
        val data = JSObject().apply {
            put("timestamp", System.currentTimeMillis())
            put("status", currentStatus)
            put("isRunning", isRunning)
        }

        // Notify listeners
        notifyListeners("heartbeat", data)
    }
}
