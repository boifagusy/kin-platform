package com.kin.app;

import android.content.Intent;
import android.location.Location;
import com.getcapacitor.JSObject;
import com.getcapacitor.Plugin;
import com.getcapacitor.PluginCall;
import com.getcapacitor.PluginMethod;
import com.getcapacitor.annotation.CapacitorPlugin;

@CapacitorPlugin(name = "KinLocation")
public class KinLocationPlugin extends Plugin {

    @PluginMethod
    public void startTracking(PluginCall call) {
        boolean emergencyMode = call.getBoolean("emergencyMode", false);

        Intent intent = new Intent(getContext(), KinLocationService.class);
        intent.putExtra("emergencyMode", emergencyMode);
        getContext().startForegroundService(intent);

        call.resolve();
    }

    @PluginMethod
    public void stopTracking(PluginCall call) {
        Intent intent = new Intent(getContext(), KinLocationService.class);
        getContext().stopService(intent);
        call.resolve();
    }

    @PluginMethod
    public void getLastLocation(PluginCall call) {
        Location location = KinLocationService.lastLocation;

        if (location == null) {
            call.resolve(new JSObject());
            return;
        }

        JSObject result = new JSObject();
        result.put("latitude", location.getLatitude());
        result.put("longitude", location.getLongitude());
        result.put("accuracy", location.getAccuracy());
        result.put("timestamp", KinLocationService.lastLocationTimestamp);
        call.resolve(result);
    }
}
