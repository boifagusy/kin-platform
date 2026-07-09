import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/react';
import { BrowserRouter } from 'react-router-dom';
import HeaderV2 from '../../../components/dashboard/HeaderV2';

describe('HeaderV2', () => {
  const renderWithRouter = (component) => {
    return render(<BrowserRouter>{component}</BrowserRouter>);
  };

  it('should render user name', () => {
    const userName = 'Test User';
    renderWithRouter(<HeaderV2 user={{ name: userName }} />);
    
    expect(screen.getByText(userName)).toBeInTheDocument();
  });

  it('should render safety status indicator', () => {
    renderWithRouter(<HeaderV2 safetyStatus="green" />);
    
    const statusIndicator = screen.getByTestId('safety-status');
    expect(statusIndicator).toHaveClass('bg-green-500');
  });

  it('should show notification badge', () => {
    renderWithRouter(<HeaderV2 notificationCount={3} />);
    
    expect(screen.getByText('3')).toBeInTheDocument();
  });
});
