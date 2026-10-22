export const ssr = false;

export function getPrerenderPaths() {
  return ['home', 'popular', 'latest', 'submit', 'mobile_submit', 'policy', 'favourites'].map(
    (path) => ({ path }),
  );
}
