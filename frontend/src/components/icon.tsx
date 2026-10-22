export function Icon({ name }: { name: string }) {
  return <span aria-hidden="true" className={`typcn typcn-${name}`} />;
}
