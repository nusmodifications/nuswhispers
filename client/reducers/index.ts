import { combineReducers } from 'redux';
import categories from './categories';
import confessions from './confessions';
import tags from './tags';

export default combineReducers({
  categories,
  confessions,
  tags,
});
