import Foundation

@objc public class KinDevice: NSObject {
    @objc public func echo(_ value: String) -> String {
        print(value)
        return value
    }
}
