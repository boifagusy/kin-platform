package com.kin.plugins.notifications;

import com.getcapacitor.Logger;

public class KinNotifications {

    public String echo(String value) {
        Logger.info("Echo", value);
        return value;
    }
}
