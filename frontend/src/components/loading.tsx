function SkeletonLine({ className = 'w-full' }: { className?: string }) {
  return <div className={`h-3 rounded bg-gray-200 ${className}`} />;
}

function CompactSkeleton() {
  return (
    <div className="space-y-3 py-2">
      <SkeletonLine className="w-3/4" />
      <SkeletonLine className="w-1/2" />
      <SkeletonLine className="w-2/3" />
    </div>
  );
}

function FormSkeleton() {
  return (
    <>
      <div className="mb-6 space-y-4">
        <div className="mb-6 h-7 w-64 max-w-full rounded bg-gray-200" />
        <SkeletonLine />
        <SkeletonLine className="w-5/6" />
        <SkeletonLine className="w-3/4" />
      </div>
      <div className="space-y-7 bg-white p-5 sm:p-6">
        <SkeletonLine className="w-24" />
        <div className="h-52 rounded-sm bg-gray-100" />
        <SkeletonLine className="w-32" />
        <div className="grid grid-cols-2 gap-4 sm:grid-cols-3">
          {Array.from({ length: 6 }, (_, index) => (
            <div key={index} className="flex items-center gap-2">
              <div className="size-4 shrink-0 rounded-xs bg-gray-200" />
              <SkeletonLine className="w-20" />
            </div>
          ))}
        </div>
        <div className="h-16 w-64 max-w-full rounded-sm bg-gray-100" />
        <div className="h-9 w-36 rounded-full bg-gray-200" />
      </div>
    </>
  );
}

function PageSkeleton() {
  return (
    <>
      <div className="mb-6 h-7 w-48 rounded bg-gray-200" />
      <div className="space-y-7 bg-white p-5 sm:p-6">
        {Array.from({ length: 3 }, (_, index) => (
          <div key={index} className="space-y-3">
            <SkeletonLine />
            <SkeletonLine />
            <SkeletonLine className="w-3/4" />
          </div>
        ))}
      </div>
    </>
  );
}

function FeedSkeleton({ count }: { count: number }) {
  return Array.from({ length: count }, (_, index) => (
    <div key={index} className="mb-4 bg-white p-5 sm:p-6 lg:px-7">
      <div className="flex items-center justify-between gap-4">
        <SkeletonLine className="w-20" />
        <SkeletonLine className="w-12" />
      </div>
      <div className="my-7 space-y-4">
        <SkeletonLine />
        <SkeletonLine />
        <SkeletonLine className="w-5/6" />
        <SkeletonLine className="w-2/3" />
      </div>
      <div className="flex items-center justify-between gap-4 py-2">
        <SkeletonLine className="w-16" />
        <div className="flex gap-3">
          <SkeletonLine className="w-8" />
          <SkeletonLine className="w-8" />
          <SkeletonLine className="w-4" />
        </div>
      </div>
    </div>
  ));
}

const skeletons = {
  feed: FeedSkeleton,
  compact: CompactSkeleton,
  form: FormSkeleton,
  page: PageSkeleton,
};
const labels = {
  feed: 'Loading confessions…',
  compact: 'Loading…',
  form: 'Loading submission form…',
  page: 'Loading…',
};

export function Loading({
  variant = 'feed',
  count = 2,
}: {
  variant?: keyof typeof skeletons;
  count?: number;
}) {
  const Skeleton = skeletons[variant];
  return (
    <output className="block" aria-busy="true">
      <span className="sr-only">{labels[variant]}</span>
      <div className="animate-pulse motion-reduce:animate-none" aria-hidden="true">
        <Skeleton count={count} />
      </div>
    </output>
  );
}
