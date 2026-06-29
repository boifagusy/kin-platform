package com.kin.plugins.device;

import com.getcapacitor.Logger;

public class KinDevice {

    public String echo(String value) {
        Logger.info("Echo", value);
        return value;
    }
}
