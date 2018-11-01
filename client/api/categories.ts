import get from 'lodash/get';
import { normalize } from 'normalizr';
import client from './client';
import { categoryList } from './schema';

export async function fetchCategories() {
  const response = await client.get('/categories');
  return normalize(get(response, 'data.categories', []), categoryList);
}
