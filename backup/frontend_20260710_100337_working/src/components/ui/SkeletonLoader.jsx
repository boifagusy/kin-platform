import React from "react";

function SkeletonLoader({ type = "card", count = 1, className = "" }) {
  const renderSkeleton = () => {
    switch (type) {
      case "card":
        return (
          <div className={`bg-white rounded-xl p-4 shadow-sm animate-pulse ${className}`}>
            <div className="flex items-center gap-3 mb-3">
              <div className="w-10 h-10 bg-gray-200 rounded-full" />
              <div className="flex-1">
                <div className="h-4 bg-gray-200 rounded w-3/4 mb-2" />
                <div className="h-3 bg-gray-200 rounded w-1/2" />
              </div>
            </div>
            <div className="h-3 bg-gray-200 rounded w-full mb-2" />
            <div className="h-3 bg-gray-200 rounded w-2/3" />
          </div>
        );

      case "contact":
        return (
          <div className={`flex items-center gap-3 p-3 bg-white rounded-lg animate-pulse ${className}`}>
            <div className="w-12 h-12 bg-gray-200 rounded-full" />
            <div className="flex-1">
              <div className="h-4 bg-gray-200 rounded w-3/4 mb-2" />
              <div className="h-3 bg-gray-200 rounded w-1/2" />
            </div>
          </div>
        );

      case "alert":
        return (
          <div className={`flex items-center gap-3 p-4 bg-white rounded-xl shadow-sm animate-pulse ${className}`}>
            <div className="w-12 h-12 bg-gray-200 rounded-full" />
            <div className="flex-1">
              <div className="h-4 bg-gray-200 rounded w-3/4 mb-2" />
              <div className="h-3 bg-gray-200 rounded w-1/2 mb-1" />
              <div className="h-2 bg-gray-200 rounded w-1/4" />
            </div>
          </div>
        );

      case "dashboard":
        return (
          <div className={`animate-pulse ${className}`}>
            {/* Header stats */}
            <div className="flex gap-3 mb-4">
              <div className="flex-1 h-20 bg-gray-200 rounded-xl" />
              <div className="flex-1 h-20 bg-gray-200 rounded-xl" />
            </div>
            {/* Map placeholder */}
            <div className="h-48 bg-gray-200 rounded-xl mb-4" />
            {/* List items */}
            <div className="space-y-3">
              <div className="h-16 bg-gray-200 rounded-xl" />
              <div className="h-16 bg-gray-200 rounded-xl" />
              <div className="h-16 bg-gray-200 rounded-xl" />
            </div>
          </div>
        );

      case "list":
        return (
          <div className={`animate-pulse ${className}`}>
            {Array.from({ length: count }).map((_, i) => (
              <div key={i} className="flex items-center gap-3 p-3 border-b border-gray-100">
                <div className="w-10 h-10 bg-gray-200 rounded-full" />
                <div className="flex-1">
                  <div className="h-4 bg-gray-200 rounded w-3/4 mb-2" />
                  <div className="h-3 bg-gray-200 rounded w-1/2" />
                </div>
              </div>
            ))}
          </div>
        );

      case "text":
        return (
          <div className={`animate-pulse ${className}`}>
            <div className="h-4 bg-gray-200 rounded w-full mb-2" />
            <div className="h-4 bg-gray-200 rounded w-5/6 mb-2" />
            <div className="h-4 bg-gray-200 rounded w-4/6" />
          </div>
        );

      case "circle":
        return (
          <div className={`w-12 h-12 bg-gray-200 rounded-full animate-pulse ${className}`} />
        );

      case "rectangle":
        return (
          <div className={`h-32 bg-gray-200 rounded-xl animate-pulse ${className}`} />
        );

      default:
        return (
          <div className={`h-16 bg-gray-200 rounded-lg animate-pulse ${className}`} />
        );
    }
  };

  if (count > 1 && (type === "list" || type === "card")) {
    return <div className="space-y-3">{Array.from({ length: count }).map((_, i) => renderSkeleton())}</div>;
  }

  return renderSkeleton();
}

export default SkeletonLoader;
