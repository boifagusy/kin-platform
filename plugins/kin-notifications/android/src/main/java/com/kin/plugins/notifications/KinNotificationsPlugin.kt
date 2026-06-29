package com.kin.plugins.notifications

import android.app.NotificationChannel
import android.app.NotificationManager
import android.app.PendingIntent
import android.content.Context
import android.content.Intent
import android.os.Build
import androidx.core.app.NotificationCompat
import androidx.core.app.NotificationManagerCompat
import androidx.work.OneTimeWorkRequest
import androidx.work.WorkManager
import androidx.work.Worker
import androidx.work.WorkerParameters
import com.getcapacitor.JSObject
import com.getcapacitor.Plugin
import com.getcapacitor.PluginCall
import com.getcapacitor.PluginMethod
import com.getcapacitor.annotation.CapacitorPlugin
import java.util.concurrent.TimeUnit

@CapacitorPlugin(name = "KinNotifications")
class KinNotificationsPlugin : Plugin() {
    private lateinit var notificationManager: NotificationManagerCompat
    private val CHANNEL_ID = "kin_notifications"
    private val CHANNEL_NAME = "KIN Notifications"
    private val NOTIFICATION_ID_BASE = 1000

    override fun load() {
        notificationManager = NotificationManagerCompat.from(getContext())
        createNotificationChannel()
    }

    @PluginMethod
    fun schedule(call: PluginCall) {
        val title = call.getString("title") ?: run {
            call.reject("Title is required")
            return
        }
        val body = call.getString("body") ?: run {
            call.reject("Body is required")
            return
        }
        val scheduleAt = call.getString("scheduleAt")
        val id = call.getString("id") ?: System.currentTimeMillis().toString()

        val notification = NotificationCompat.Builder(getContext(), CHANNEL_ID)
            .setContentTitle(title)
            .setContentText(body)
            .setSmallIcon(android.R.drawable.ic_dialog_info)
            .setPriority(NotificationCompat.PRIORITY_HIGH)
            .setAutoCancel(true)
            .build()

        if (scheduleAt != null && scheduleAt.isNotEmpty()) {
            try {
                val time = scheduleAt.toLong()
                val workRequest = OneTimeWorkRequest.Builder(NotificationWorker::class.java)
                    .setInitialDelay(time - System.currentTimeMillis(), TimeUnit.MILLISECONDS)
                    .addTag("kin_notification_$id")
                    .build()
                
                WorkManager.getInstance(getContext()).enqueue(workRequest)
                call.resolve(JSObject().apply {
                    put("id", id)
                    put("scheduled", true)
                })
            } catch (e: Exception) {
                call.reject("Failed to schedule: ${e.message}")
            }
        } else {
            val notificationId = (NOTIFICATION_ID_BASE + System.currentTimeMillis() % 1000).toInt()
            notificationManager.notify(notificationId, notification)
            call.resolve(JSObject().apply {
                put("id", id)
                put("scheduled", false)
            })
        }
    }

    @PluginMethod
    fun cancel(call: PluginCall) {
        val notificationId = call.getString("id") ?: run {
            call.reject("ID is required")
            return
        }

        try {
            WorkManager.getInstance(getContext()).cancelAllWorkByTag("kin_notification_$notificationId")
            call.resolve(JSObject().apply { put("success", true) })
        } catch (e: Exception) {
            call.reject("Failed to cancel: ${e.message}")
        }
    }

    @PluginMethod
    fun cancelAll(call: PluginCall) {
        try {
            WorkManager.getInstance(getContext()).cancelAllWork()
            call.resolve(JSObject().apply { put("success", true) })
        } catch (e: Exception) {
            call.reject("Failed to cancel all: ${e.message}")
        }
    }

    @PluginMethod
    fun checkPermissions(call: PluginCall) {
        val result = JSObject().apply {
            put("granted", notificationManager.areNotificationsEnabled())
        }
        call.resolve(result)
    }

    @PluginMethod
    fun requestPermissions(call: PluginCall) {
        // On Android, notification permissions are requested automatically
        val result = JSObject().apply {
            put("granted", notificationManager.areNotificationsEnabled())
        }
        call.resolve(result)
    }

    private fun createNotificationChannel() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            val channel = NotificationChannel(
                CHANNEL_ID,
                CHANNEL_NAME,
                NotificationManager.IMPORTANCE_HIGH
            ).apply {
                description = "KIN safety notifications"
                enableVibration(true)
                vibrationPattern = longArrayOf(100, 50, 100)
            }
            val manager = getContext().getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager
            manager.createNotificationChannel(channel)
        }
    }
}

// Worker for scheduled notifications
class NotificationWorker(context: Context, params: WorkerParameters) : Worker(context, params) {
    override fun doWork(): Result {
        // Show notification
        val notificationManager = NotificationManagerCompat.from(applicationContext)
        val notification = NotificationCompat.Builder(applicationContext, "kin_notifications")
            .setContentTitle("KIN Reminder")
            .setContentText("Time to check in with KIN")
            .setSmallIcon(android.R.drawable.ic_dialog_info)
            .setPriority(NotificationCompat.PRIORITY_HIGH)
            .setAutoCancel(true)
            .build()
        
        notificationManager.notify(System.currentTimeMillis().toInt(), notification)
        return Result.success()
    }
}
