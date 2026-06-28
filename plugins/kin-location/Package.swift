// swift-tools-version: 5.9
import PackageDescription

let package = Package(
    name: "KinLocation",
    platforms: [.iOS(.v15)],
    products: [
        .library(
            name: "KinLocation",
            targets: ["KinLocationPlugin"])
    ],
    dependencies: [
        .package(url: "https://github.com/ionic-team/capacitor-swift-pm.git", from: "8.0.0")
    ],
    targets: [
        .target(
            name: "KinLocationPlugin",
            dependencies: [
                .product(name: "Capacitor", package: "capacitor-swift-pm"),
                .product(name: "Cordova", package: "capacitor-swift-pm")
            ],
            path: "ios/Sources/KinLocationPlugin"),
        .testTarget(
            name: "KinLocationPluginTests",
            dependencies: ["KinLocationPlugin"],
            path: "ios/Tests/KinLocationPluginTests")
    ]
)