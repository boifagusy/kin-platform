# KIN Location Plugin - ProGuard Rules

# Keep plugin classes
-keep class com.kin.plugins.location.** { *; }
-keep class com.getcapacitor.** { *; }
-keep class com.google.android.gms.location.** { *; }
-keep class com.google.android.gms.common.** { *; }

# Kotlin reflection
-keep class kotlin.reflect.** { *; }
-keep class kotlin.Metadata { *; }
-keepclassmembers class kotlin.Metadata {
    public *;
}

# Serialization
-keepclassmembers class * {
    @com.google.gson.annotations.SerializedName <fields>;
}

# Keep all plugin methods
-keepclassmembers class * {
    @com.getcapacitor.PluginMethod <methods>;
}

# AndroidX
-keep class androidx.core.app.NotificationCompat { *; }
-keep class androidx.core.app.NotificationManagerCompat { *; }
