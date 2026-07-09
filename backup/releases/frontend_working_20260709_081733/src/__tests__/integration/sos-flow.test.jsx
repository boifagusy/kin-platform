import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { BrowserRouter } from 'react-router-dom';
import { AuthContext } from '../../context/AuthContext';
import DashboardScreenV2 from '../../screens/ui-polish/DashboardScreenV2';
import { api } from '../../services/api';

vi.mock('../../services/api');

describe('SOS Flow Integration', () => {
  const mockUser = {
    id: 1,
    name: 'Test User',
    phone: '+2348012345678'
  };

  const renderWithContext = () => {
    return render(
      <AuthContext.Provider value={{ user: mockUser, loading: false }}>
        <BrowserRouter>
          <DashboardScreenV2 />
        </BrowserRouter>
      </AuthContext.Provider>
    );
  };

  beforeEach(() => {
    vi.clearAllMocks();
    // Mock successful SOS response
    api.sos.trigger = vi.fn().mockResolvedValue({ success: true, eventId: 1 });
  });

  it('should trigger SOS when button clicked', async () => {
    renderWithContext();
    
    // Find and click SOS button
    const sosButton = screen.getByText(/SOS/i);
    fireEvent.click(sosButton);
    
    // Should open emergency modal
    await waitFor(() => {
      expect(screen.getByText(/Emergency SOS/i)).toBeInTheDocument();
    });
    
    // Click trigger emergency
    const triggerButton = screen.getByText(/Trigger Emergency Alert/i);
    fireEvent.click(triggerButton);
    
    await waitFor(() => {
      expect(api.sos.trigger).toHaveBeenCalled();
    });
  });

  it('should show confirmation after SOS triggered', async () => {
    renderWithContext();
    
    // Trigger SOS
    const sosButton = screen.getByText(/SOS/i);
    fireEvent.click(sosButton);
    
    const triggerButton = screen.getByText(/Trigger Emergency Alert/i);
    fireEvent.click(triggerButton);
    
    await waitFor(() => {
      expect(screen.getByText(/Alert Sent/i)).toBeInTheDocument();
    });
  });
});
