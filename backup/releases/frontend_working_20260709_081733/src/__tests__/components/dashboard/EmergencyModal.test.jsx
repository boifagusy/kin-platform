import { describe, it, expect, vi } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import EmergencyModal from '../../../components/dashboard/EmergencyModal';

describe('EmergencyModal', () => {
  const mockOnClose = vi.fn();
  const mockOnSOS = vi.fn();

  it('should render emergency modal when open', () => {
    render(
      <EmergencyModal 
        isOpen={true} 
        onClose={mockOnClose} 
        onSOS={mockOnSOS}
      />
    );
    
    expect(screen.getByText(/Emergency SOS/i)).toBeInTheDocument();
    expect(screen.getByText(/Trigger Emergency Alert/i)).toBeInTheDocument();
  });

  it('should not render when closed', () => {
    render(
      <EmergencyModal 
        isOpen={false} 
        onClose={mockOnClose} 
        onSOS={mockOnSOS}
      />
    );
    
    expect(screen.queryByText(/Emergency SOS/i)).not.toBeInTheDocument();
  });

  it('should trigger SOS on button click', () => {
    render(
      <EmergencyModal 
        isOpen={true} 
        onClose={mockOnClose} 
        onSOS={mockOnSOS}
      />
    );
    
    const sosButton = screen.getByText(/Trigger Emergency Alert/i);
    fireEvent.click(sosButton);
    
    expect(mockOnSOS).toHaveBeenCalled();
  });

  it('should close on cancel', () => {
    render(
      <EmergencyModal 
        isOpen={true} 
        onClose={mockOnClose} 
        onSOS={mockOnSOS}
      />
    );
    
    const cancelButton = screen.getByText(/Cancel/i);
    fireEvent.click(cancelButton);
    
    expect(mockOnClose).toHaveBeenCalled();
  });
});
