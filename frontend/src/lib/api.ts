import { clientRoot, createClient, valueMutation, view, type Transport } from '@nkzw/fate';
import { config } from './config';
import { feedEndpoint, type FeedRoute } from './routes';

export type Category = { __typename: 'Category'; id: string; name: string };
export type Tag = { __typename: 'Tag'; id: string; name: string };
export type Confession = {
  __typename: 'Confession';
  id: string;
  content: string;
  image: string | null;
  categories: Category[];
  fbPostId: string;
  updatedAt: number;
  likes: number;
  comments: number;
  favouriteIds: string[];
};
type RawCategory = { confession_category_id: string | number; confession_category: string };
type RawTag = { confession_tag_id: string | number; confession_tag: string };
export type RawConfession = {
  confession_id: string | number;
  content: string;
  images?: string | null;
  categories?: RawCategory[];
  fb_post_id?: string | number | null;
  status_updated_at_timestamp?: number;
  status_updated_at?: string;
  fb_like_count?: number;
  fb_comment_count?: number;
  favourites?: { fb_user_id: string | number }[];
  facebook_information?: {
    likes?: { summary?: { total_count?: number } };
    comments?: { data?: unknown[]; summary?: { total_count?: number } };
  };
};
type Envelope = {
  success?: boolean;
  message?: string;
  errors?: unknown;
  data?: {
    confessions?: RawConfession[];
    confession?: RawConfession;
    categories?: RawCategory[];
    tags?: RawTag[];
    errors?: unknown;
  };
  [key: string]: unknown;
};
export type Submission = {
  content: string;
  categories: string[];
  captcha: string;
  fingerprint?: string;
};
type Mutations = {
  submit: { input: Submission; output: Envelope };
  favourite: { input: { id: string; remove: boolean }; output: Envelope };
};

export class ApiError extends Error {
  constructor(
    message: string,
    public readonly status = 0,
  ) {
    super(message);
    this.name = 'ApiError';
  }
}

export function errorMessage(errors: unknown): string {
  if (typeof errors === 'string') return errors;
  if (Array.isArray(errors)) return errors.map(errorMessage).filter(Boolean).join(' ');
  if (errors && typeof errors === 'object')
    return Object.values(errors).map(errorMessage).filter(Boolean).join(' ');
  return '';
}

const category = (raw: RawCategory): Category => ({
  __typename: 'Category',
  id: String(raw.confession_category_id),
  name: raw.confession_category,
});
export function normalizeConfession(raw: RawConfession): Confession {
  return {
    __typename: 'Confession',
    id: String(raw.confession_id),
    content: raw.content,
    image: raw.images || null,
    categories: (raw.categories || []).map(category),
    fbPostId: String(raw.fb_post_id || ''),
    updatedAt:
      Number(raw.status_updated_at_timestamp) ||
      Date.parse(raw.status_updated_at || '') / 1000 ||
      0,
    likes: Number(raw.facebook_information?.likes?.summary?.total_count ?? raw.fb_like_count ?? 0),
    comments: Number(
      raw.facebook_information?.comments?.summary?.total_count ??
        raw.facebook_information?.comments?.data?.length ??
        raw.fb_comment_count ??
        0,
    ),
    favouriteIds: (raw.favourites || []).map((item) => String(item.fb_user_id)),
  };
}

export function createLaravelTransport(
  baseUrl = config.apiUrl,
  fetcher: typeof fetch = fetch,
): Transport<Mutations> {
  async function request(path: string, init: RequestInit = {}): Promise<Envelope> {
    const response = await fetcher(`${baseUrl}${path}`, {
      ...init,
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...init.headers,
      },
    });
    const body: Envelope = await response.json().catch(() => {
      throw new ApiError(
        'The server returned an unreadable response. Please try again.',
        response.status,
      );
    });
    if (!response.ok || body.success === false) {
      throw new ApiError(
        errorMessage(body.errors || body.data?.errors) ||
          body.message ||
          (response.status === 429
            ? 'Too many requests. Please try again shortly.'
            : 'Could not complete the request. Please try again.'),
        response.status,
      );
    }
    return body;
  }

  return {
    async fetchById(type, ids, _select, _args, context) {
      if (type !== 'Confession') throw new ApiError(`Unsupported entity: ${type}`);
      return Promise.all(
        ids.map(async (id) => {
          const body = await request(`/confessions/${encodeURIComponent(id)}`, {
            signal: context?.signal,
          });
          if (!body.data?.confession)
            throw new ApiError('This confession could not be found.', 404);
          return normalizeConfession(body.data.confession);
        }),
      );
    },
    async fetchList(proc, _select, args = {}, context) {
      if (proc === 'categories' || proc === 'tags') {
        const body = await request(proc === 'categories' ? '/categories' : '/tags/top/5', {
          signal: context?.signal,
        });
        const nodes =
          proc === 'categories'
            ? (body.data?.categories || []).map(category)
            : (body.data?.tags || []).map((tag): Tag => ({
                __typename: 'Tag',
                id: String(tag.confession_tag_id),
                name: tag.confession_tag,
              }));
        return {
          items: nodes.map((node) => ({ cursor: undefined, node })),
          pagination: { hasNext: false, hasPrevious: false },
        };
      }
      if (proc !== 'confessions') throw new ApiError(`Unsupported list: ${proc}`);
      const route: FeedRoute = {
        kind: args.kind as FeedRoute['kind'],
        value: args.value as string | undefined,
      };
      const offset = Number(args.after || 0);
      const count = Number(args.first || 5);
      const params = new URLSearchParams({
        timestamp: String(args.timestamp),
        offset: String(offset),
        count: String(count),
      });
      const body = await request(`${feedEndpoint(route)}?${params}`, { signal: context?.signal });
      const nodes = (body.data?.confessions || []).map(normalizeConfession);
      return {
        items: nodes.map((node, index) => ({ cursor: String(offset + index + 1), node })),
        pagination: {
          hasNext: nodes.length === count,
          hasPrevious: false,
          nextCursor: String(offset + count),
        },
      };
    },
    async mutate(proc, input) {
      const data = new URLSearchParams();
      let path: string;
      if (proc === 'submit') {
        const submission = input as Submission;
        path = '/confessions';
        data.set('content', submission.content);
        data.set('captcha', submission.captcha);
        submission.categories.forEach((id, index) => data.append(`categories[${index}]`, id));
        if (config.fingerprintApiKey && submission.fingerprint)
          data.set(config.fingerprintApiKey, submission.fingerprint);
      } else {
        const favourite = input as Mutations['favourite']['input'];
        path = `/fbuser/${favourite.remove ? 'unfavourite' : 'favourite'}`;
        data.set('confession_id', favourite.id);
      }
      return request(path, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
        body: data,
      });
    },
  };
}

export const CategoryView = view<Category>()({ id: true, name: true });
export const TagView = view<Tag>()({ id: true, name: true });
export const ConfessionView = view<Confession>()({
  id: true,
  content: true,
  image: true,
  categories: CategoryView,
  fbPostId: true,
  updatedAt: true,
  likes: true,
  comments: true,
  favouriteIds: true,
});
export const ConfessionConnection = {
  items: { cursor: true, node: ConfessionView },
  pagination: { hasNext: true, nextCursor: true, hasPrevious: true },
} as const;

const roots = {
  confessions: clientRoot<Confession[], 'Confession'>('Confession'),
  categories: clientRoot<Category[], 'Category'>('Category'),
  tags: clientRoot<Tag[], 'Tag'>('Tag'),
};
const mutations = {
  submit: valueMutation<Submission, Envelope>(),
  favourite: valueMutation<Mutations['favourite']['input'], Envelope>(),
};
export type AppSchema = [typeof roots, typeof mutations];

export function createAppClient(transport = createLaravelTransport()) {
  return createClient<AppSchema>({
    roots,
    mutations,
    transport,
    types: [
      { type: 'Confession', fields: { categories: { listOf: 'Category', array: true } } },
      { type: 'Category' },
      { type: 'Tag' },
    ],
  });
}
