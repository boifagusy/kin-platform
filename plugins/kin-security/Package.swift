// swift-tools-version: 5.9
import PackageDescription

let package = Package(
    name: "KinSecurity",
    platforms: [.iOS(.v15)],
    products: [
        .library(
            name: "KinSecurity",
            targets: ["KinSecurityPlugin"])
    ],
    dependencies: [
        .package(url: "https://github.com/ionic-team/capacitor-swift-pm.git", from: "8.0.0")
    ],
    targets: [
        .target(
            name: "KinSecurityPlugin",
            dependencies: [
                .product(name: "Capacitor", package: "capacitor-swift-pm"),
                .product(name: "Cordova", package: "capacitor-swift-pm")
            ],
            path: "ios/Sources/KinSecurityPlugin"),
        .testTarget(
            name: "KinSecurityPluginTests",
            dependencies: ["KinSecurityPlugin"],
            path: "ios/Tests/KinSecurityPluginTests")
    ]
)