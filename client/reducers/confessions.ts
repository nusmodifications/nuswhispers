import { makeReducer } from './utils';

export interface Confession {
  categories: string[];
  confessionId: string;
  content: string;
  createdAt: string;
  facebookInformation: string;
  favourites: string[];
  fbCommentCount: number;
  fbLikeCount: number;
  fbPostId: number;
  images: string | null;
  status: string;
  statusUpdatedAt: string;
  statusUpdatedAtTimestamp: string;
  updatedAt: string;
  views: number;
}

export default makeReducer({}, {});
