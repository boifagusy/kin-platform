import Foundation

@objc public class KinSecurity: NSObject {
    @objc public func echo(_ value: String) -> String {
        print(value)
        return value
    }
}
