import Foundation

@objc public class KinNetwork: NSObject {
    @objc public func echo(_ value: String) -> String {
        print(value)
        return value
    }
}
