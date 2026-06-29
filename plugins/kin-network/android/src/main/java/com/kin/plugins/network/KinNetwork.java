package com.kin.plugins.network;

import com.getcapacitor.Logger;

public class KinNetwork {

    public String echo(String value) {
        Logger.info("Echo", value);
        return value;
    }
}
