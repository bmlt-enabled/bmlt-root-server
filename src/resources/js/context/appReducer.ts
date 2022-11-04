interface State {
  displayName: string;
}

interface Action {
  type: string;
  payload: any;
}

const appReducer = (state: State, action: Action): State => {
  switch (action.type) {
    case 'SET_DISPLAY_NAME':
      return {
        ...state,
        displayName: action.payload,
      };
    default:
      return state;
  }
};

export default appReducer;
