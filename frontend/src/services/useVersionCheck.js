import { useState, useEffect } from 'react';

export function useVersionCheck() {
  const [updateData, setUpdateData] = useState(null);
  const [showDialog, setShowDialog] = useState(false);

  useEffect(() => {
    const check = async () => {
      try {
        const res = await fetch('/api/v1/version?current=1&platform=android');
        const data = await res.json();
        if (data.update_available) {
          setUpdateData(data);
          setShowDialog(true);
        }
      } catch (e) {
        // Silent — no dialog on error
      }
    };
    check();
  }, []);

  const handleDismiss = () => setShowDialog(false);

  return { showDialog, updateData, handleDismiss };
}
