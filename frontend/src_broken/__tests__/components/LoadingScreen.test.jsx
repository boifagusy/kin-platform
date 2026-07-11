import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/react';
import LoadingScreen from '../../components/ui/LoadingScreen';

describe('LoadingScreen', () => {
  it('should render loading spinner', () => {
    render(<LoadingScreen />);
    
    expect(screen.getByRole('status')).toBeInTheDocument();
    expect(screen.getByText(/Loading/i)).toBeInTheDocument();
  });

  it('should show custom message', () => {
    const customMessage = 'Checking your safety status...';
    render(<LoadingScreen message={customMessage} />);
    
    expect(screen.getByText(customMessage)).toBeInTheDocument();
  });

  it('should show progress bar when progress prop provided', () => {
    render(<LoadingScreen progress={75} />);
    
    expect(screen.getByRole('progressbar')).toBeInTheDocument();
    expect(screen.getByText('75%')).toBeInTheDocument();
  });
});
