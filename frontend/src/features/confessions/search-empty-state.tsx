export function SearchEmptyState() {
  return (
    <div className="px-4 py-10 text-center sm:py-14">
      <svg className="mx-auto mb-5 h-32 w-44" viewBox="0 0 176 128" aria-hidden="true">
        <path
          className="fill-sky-100"
          d="M25 36C30 15 59 10 83 18s48 9 53 34-9 52-38 56-53-2-68-20-10-33-5-52Z"
        />
        <path
          className="fill-white stroke-gray-300"
          d="M37 31c0-7 5-11 12-11h56c8 0 13 5 13 12v35c0 7-5 12-13 12H66L48 93l3-14h-2c-8 0-13-5-13-12Z"
          strokeWidth="2"
          strokeLinejoin="round"
        />
        <path
          className="stroke-gray-300"
          d="m52 39 33-1m-33 12 43 1m-43 11 21-1"
          fill="none"
          strokeWidth="3"
          strokeLinecap="round"
        />
        <path className="stroke-brand" d="m116 91 20 22" strokeWidth="10" strokeLinecap="round" />
        <circle className="fill-sky-50 stroke-brand" cx="97" cy="70" r="29" strokeWidth="4" />
        <path
          className="fill-sky-100 stroke-brand"
          d="M97 82 85 71c-9-9 4-19 12-10 8-9 21 1 12 10Z"
          strokeWidth="2"
          strokeLinejoin="round"
        />
        <path
          className="stroke-gray-400"
          d="m143 31-1 10m-5-5 11 1M18 82l-3 6m-2-5 7 4"
          fill="none"
          strokeWidth="2"
          strokeLinecap="round"
        />
        <circle className="fill-brand" cx="153" cy="77" r="2" />
        <circle className="fill-gray-300" cx="68" cy="112" r="2" />
      </svg>
      <p className="mx-auto max-w-64 text-sm leading-7 text-gray-500">
        Start with a word, a phrase or a confession number.
      </p>
    </div>
  );
}
