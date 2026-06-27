package com.kin.app

import com.getcapacitor.BridgeActivity
import com.kin.app.plugins.KinSafetyPlugin

class MainActivity : BridgeActivity() {
    override fun onCreate(savedInstanceState: android.os.Bundle?) {
        // Register KinSafetyPlugin
        registerPlugin(KinSafetyPlugin::class.java)
        super.onCreate(savedInstanceState)
    }
}
