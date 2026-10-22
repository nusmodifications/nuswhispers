import { Button } from '@base-ui/react/button';
import { Component, type ReactNode } from 'react';

export function Icon({ name }: { name: string }) {
  return <span aria-hidden="true" className={`typcn typcn-${name}`} />;
}

export function Loading() {
  return (
    <output className="loading block">
      <span className="sr-only">Loading…</span>
    </output>
  );
}

export class ErrorBoundary extends Component<{ children: ReactNode }, { error: Error | null }> {
  state: { error: Error | null } = { error: null };
  static getDerivedStateFromError(error: Error) {
    return { error };
  }
  render() {
    if (this.state.error)
      return (
        <div className="notice error" role="alert">
          <p>{this.state.error.message || 'Could not load this page.'}</p>
          <Button className="button mt-3" onClick={() => window.location.reload()}>
            Try again
          </Button>
        </div>
      );
    return this.props.children;
  }
}
