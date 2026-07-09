import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { BrowserRouter } from 'react-router-dom';
import { AuthContext } from '../../context/AuthContext';
import DashboardScreenV2 from '../../screens/ui-polish/DashboardScreenV2';
import { api } from '../../services/api';

vi.mock('../../services/api');

describe('Check-in Flow Integration', () => {
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
    api.checkin.store = vi.fn().mockResolvedValue({ success: true, id: 1, confidence: 85 });
  });

  it('should perform check-in when button clicked', async () => {
    renderWithContext();
    
    const checkInButton = screen.getByText(/Check In/i);
    fireEvent.click(checkInButton);
    
    await waitFor(() => {
      expect(api.checkin.store).toHaveBeenCalled();
    });
  });

  it('should show confirmation after check-in', async () => {
    renderWithContext();
    
    const checkInButton = screen.getByText(/Check In/i);
    fireEvent.click(checkInButton);
    
    await waitFor(() => {
      expect(screen.getByText(/Check-in Successful/i)).toBeInTheDocument();
    });
  });

  it('should handle check-in errors', async () => {
    api.checkin.store = vi.fn().mockRejectedValue({ response: { status: 500 } });
    
    renderWithContext();
    
    const checkInButton = screen.getByText(/Check In/i);
    fireEvent.click(checkInButton);
    
    await waitFor(() => {
      expect(screen.getByText(/Error/i)).toBeInTheDocument();
    });
  });
});
