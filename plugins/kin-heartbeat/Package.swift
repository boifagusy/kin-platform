// swift-tools-version: 5.9
import PackageDescription

let package = Package(
    name: "KinHeartbeat",
    platforms: [.iOS(.v15)],
    products: [
        .library(
            name: "KinHeartbeat",
            targets: ["KinHeartbeatPlugin"])
    ],
    dependencies: [
        .package(url: "https://github.com/ionic-team/capacitor-swift-pm.git", from: "8.0.0")
    ],
    targets: [
        .target(
            name: "KinHeartbeatPlugin",
            dependencies: [
                .product(name: "Capacitor", package: "capacitor-swift-pm"),
                .product(name: "Cordova", package: "capacitor-swift-pm")
            ],
            path: "ios/Sources/KinHeartbeatPlugin"),
        .testTarget(
            name: "KinHeartbeatPluginTests",
            dependencies: ["KinHeartbeatPlugin"],
            path: "ios/Tests/KinHeartbeatPluginTests")
    ]
)