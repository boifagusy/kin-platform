import Foundation

@objc public class KinHeartbeat: NSObject {
    @objc public func echo(_ value: String) -> String {
        print(value)
        return value
    }
}
