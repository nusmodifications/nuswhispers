import { schema } from 'normalizr';

export const category: schema.Entity = new schema.Entity(
  'category',
  {},
  {
    idAttribute: 'confessionCategoryId',
  },
);

export const categoryList: schema.Entity[] = [category];

export const confession: schema.Entity = new schema.Entity(
  'confession',
  {
    categories: categoryList,
  },
  {
    idAttribute: 'confessionId',
  },
);

export const confessionList: schema.Entity[] = [confession];

export const tag = new schema.Entity(
  'tag',
  {},
  {
    idAttribute: 'confessionTagId',
  },
);

export const tagList: schema.Entity[] = [tag];
