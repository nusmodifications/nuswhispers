import type { CheckedRequest, Request } from '@nkzw/fate';
import { useRequest } from 'react-fate';
import type { AppSchema } from './api';

// This app uses a REST adapter rather than Fate's generated server module.
export function useAppRequest<const R extends Request>(request: CheckedRequest<AppSchema[0], R>) {
  return useRequest<R, AppSchema[0]>(request);
}
