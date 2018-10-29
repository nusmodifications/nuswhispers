import { makeReducer } from './utils';

export interface Tag {
  confessionTagId: string;
  confessionTag: string;
  popularityRating?: number;
}

export default makeReducer({}, {});
