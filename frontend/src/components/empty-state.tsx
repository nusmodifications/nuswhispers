import type { ReactNode } from 'react';

export function EmptyState({ title, children }: { title: string; children: ReactNode }) {
  return (
    <div className="bg-white p-6 sm:p-8">
      <h2 className="mb-3 text-lg font-normal">{title}</h2>
      <p className="text-sm leading-7 text-gray-500">{children}</p>
    </div>
  );
}
