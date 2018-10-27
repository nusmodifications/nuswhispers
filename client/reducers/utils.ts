import { Action, Reducer } from 'redux';

export const makeReducer = <S>(initialState: S, handlers: { [key: string]: Reducer<S> }): Reducer<S> => {
  return function reducer<A extends Action>(state: S = initialState, action: A): S {
    return typeof handlers[action.type] === 'function' ? handlers[action.type](state, action) : state;
  };
}
