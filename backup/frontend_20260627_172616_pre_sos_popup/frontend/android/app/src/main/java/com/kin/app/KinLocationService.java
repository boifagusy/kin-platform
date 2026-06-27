package com.kin.app;

import android.app.Notification;
import android.app.NotificationChannel;
import android.app.NotificationManager;
import android.app.Service;
import android.content.Intent;
import android.location.Location;
import android.os.Build;
import android.os.IBinder;
import androidx.annotation.Nullable;
import androidx.core.app.NotificationCompat;
import com.google.android.gms.location.FusedLocationProviderClient;
import com.google.android.gms.location.LocationCallback;
import com.google.android.gms.location.LocationRequest;
import com.google.android.gms.location.LocationResult;
import com.google.android.gms.location.LocationServices;
import com.google.android.gms.location.Priority;

public class KinLocationService extends Service {

    public static final String CHANNEL_ID = "kin_location_channel";
    public static final int NOTIFICATION_ID = 9001;

    // Update intervals, in milliseconds.
    public static final long NORMAL_INTERVAL_MS = 5 * 60 * 1000;      // 5 minutes
    public static final long EMERGENCY_INTERVAL_MS = 20 * 1000;       // 20 seconds

    public static volatile Location lastLocation = null;
    public static volatile long lastLocationTimestamp = 0;

    private FusedLocationProviderClient fusedLocationClient;
    private LocationCallback locationCallback;
    private boolean emergencyMode = false;

    @Override
    public void onCreate() {
        super.onCreate();
        fusedLocationClient = LocationServices.getFusedLocationProviderClient(this);

        locationCallback = new LocationCallback() {
            @Override
            public void onLocationResult(LocationResult result) {
                if (result == null) return;
                Location location = result.getLastLocation();
                if (location != null) {
                    lastLocation = location;
                    lastLocationTimestamp = System.currentTimeMillis();
                }
            }
        };
    }

    @Override
    public int onStartCommand(Intent intent, int flags, int startId) {
        boolean requestEmergencyMode = intent != null && intent.getBooleanExtra("emergencyMode", false);
        emergencyMode = requestEmergencyMode;

        createNotificationChannel();
        startForeground(NOTIFICATION_ID, buildNotification());
        startLocationUpdates();

        return START_STICKY;
    }

    private void startLocationUpdates() {
        long intervalMs = emergencyMode ? EMERGENCY_INTERVAL_MS : NORMAL_INTERVAL_MS;

        LocationRequest locationRequest = new LocationRequest.Builder(intervalMs)
                .setPriority(Priority.PRIORITY_HIGH_ACCURACY)
                .setMinUpdateIntervalMillis(intervalMs / 2)
                .build();

        try {
            fusedLocationClient.removeLocationUpdates(locationCallback);
            fusedLocationClient.requestLocationUpdates(
                    locationRequest,
                    locationCallback,
                    getMainLooper()
            );
        } catch (SecurityException e) {
            // Location permission not granted; the JS side is responsible for
            // requesting permissions before starting this service.
        }
    }

    private Notification buildNotification() {
        String text = emergencyMode
                ? "Emergency tracking active — sharing your location frequently."
                : "KIN is tracking your location for your safety.";

        return new NotificationCompat.Builder(this, CHANNEL_ID)
                .setContentTitle("KIN Safety Tracking")
                .setContentText(text)
                .setSmallIcon(android.R.drawable.ic_menu_mylocation)
                .setOngoing(true)
                .build();
    }

    private void createNotificationChannel() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            NotificationChannel channel = new NotificationChannel(
                    CHANNEL_ID,
                    "Background Tracking",
                    NotificationManager.IMPORTANCE_LOW
            );
            NotificationManager manager = getSystemService(NotificationManager.class);
            if (manager != null) {
                manager.createNotificationChannel(channel);
            }
        }
    }

    @Override
    public void onDestroy() {
        super.onDestroy();
        if (fusedLocationClient != null && locationCallback != null) {
            fusedLocationClient.removeLocationUpdates(locationCallback);
        }
    }

    @Nullable
    @Override
    public IBinder onBind(Intent intent) {
        return null;
    }
}
