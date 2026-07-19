import { ModalBackdrop, ModalContent } from '../../motion/modal';
import Button from './Button';

export default function Dialog({ open, title, message, confirmLabel = 'Confirm', cancelLabel = 'Cancel', onConfirm, onCancel, destructive = false }) {
  if (!open) return null;

  return (
    <ModalBackdrop onClose={onCancel}>
      <ModalContent className="bg-white rounded-2xl w-80 p-6 text-center">
        <h3 className="text-lg font-bold text-gray-900 mb-2">{title}</h3>
        <p className="text-sm text-gray-500 mb-6">{message}</p>
        <div className="flex gap-3">
          <Button variant="secondary" size="md" onClick={onCancel} className="flex-1">{cancelLabel}</Button>
          <Button variant={destructive ? 'danger' : 'primary'} size="md" onClick={onConfirm} className="flex-1">{confirmLabel}</Button>
        </div>
      </ModalContent>
    </ModalBackdrop>
  );
}
