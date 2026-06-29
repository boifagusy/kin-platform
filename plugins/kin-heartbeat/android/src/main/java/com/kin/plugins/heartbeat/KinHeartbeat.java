package com.kin.plugins.heartbeat;

import com.getcapacitor.Logger;

public class KinHeartbeat {

    public String echo(String value) {
        Logger.info("Echo", value);
        return value;
    }
}
