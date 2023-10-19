import DarkModeIcon from '@mui/icons-material/DarkMode';
import LightModeIcon from '@mui/icons-material/LightMode';
import { IconButton } from '@mui/material';
import { styled } from '@mui/system';
import { useContext } from 'react';

import { ActionType, AppContext } from '../../AppContext';
import { setMode } from '../../darkmode';

const StyledButton = styled(IconButton)(({ theme }) => ({
  color: theme.palette.common.white,
  marginRight: theme.spacing(0.5),
  marginLeft: theme.spacing(0.5),
}));
export const DarkmodeSelect = () => {
  const { state, dispatch } = useContext(AppContext);

  const handleDarkMode = () => {
    const mode = state.mode === 'light' ? 'dark' : 'light';
    dispatch({ type: ActionType.SET_MODE, payload: mode });
    setMode(mode);
  };
  return <StyledButton onClick={handleDarkMode}>{state.mode === 'light' ? <DarkModeIcon /> : <LightModeIcon />}</StyledButton>;
};
