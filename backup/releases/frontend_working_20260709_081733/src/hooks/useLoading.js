import { useState, useCallback } from "react";

function useLoading(initialState = false) {
  const [isLoading, setIsLoading] = useState(initialState);
  const [loadingMessage, setLoadingMessage] = useState("default");

  const startLoading = useCallback((message = "default") => {
    setLoadingMessage(message);
    setIsLoading(true);
  }, []);

  const stopLoading = useCallback(() => {
    setIsLoading(false);
    setLoadingMessage("default");
  }, []);

  const withLoading = useCallback(async (callback, message = "default") => {
    try {
      startLoading(message);
      const result = await callback();
      return result;
    } finally {
      stopLoading();
    }
  }, [startLoading, stopLoading]);

  return {
    isLoading,
    loadingMessage,
    startLoading,
    stopLoading,
    withLoading
  };
}

export default useLoading;
