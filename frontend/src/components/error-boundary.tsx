import { Button } from '@base-ui/react/button';
import { Component, type ReactNode } from 'react';
import { primaryActionClasses } from './primary-action';

export class ErrorBoundary extends Component<{ children: ReactNode }, { error: Error | null }> {
  state: { error: Error | null } = { error: null };
  static getDerivedStateFromError(error: Error) {
    return { error };
  }
  render() {
    if (this.state.error)
      return (
        <div className="mb-4 bg-red-50 p-5 text-sm text-red-800" role="alert">
          <p>{this.state.error.message || 'Could not load this page.'}</p>
          <Button
            className={`${primaryActionClasses} mt-3`}
            onClick={() => window.location.reload()}
          >
            Try again
          </Button>
        </div>
      );
    return this.props.children;
  }
}
