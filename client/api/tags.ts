import get from 'lodash/get';
import { normalize } from 'normalizr';
import client from './client';
import { tagList } from './schema';

export async function fetchTopTags(count: number = 5) {
  const response = await client.get(`/tags/top/${count}`);
  return normalize(get(response, 'data.tags', []), tagList);
}
