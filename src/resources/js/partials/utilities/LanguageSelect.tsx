import { FormControl, MenuItem, Select, SelectChangeEvent } from '@mui/material';
import { styled } from '@mui/system';
import { useContext } from 'react';

import { ActionType, AppContext } from '../../AppContext';

const StyledFormControl = styled(FormControl)(({ theme }) => ({
  borderRadius: theme.shape.borderRadius * 1,
  minWidth: 120,
  backgroundColor: '#ddd',
}));

export const LanguageSelect = () => {
  const languageArr = Object.entries(settings.languageMapping);
  const { state, dispatch } = useContext(AppContext);

  const handleLanguageChange = (e: SelectChangeEvent) => {
    dispatch({ type: ActionType.SET_LANGUAGE, payload: e.target.value });
  };
  return (
    <StyledFormControl size='small'>
      <Select labelId='language-select-label' id='language-select' value={state.language} onChange={handleLanguageChange}>
        {languageArr.map((lang) => (
          <MenuItem key={lang[0]} value={lang[0]}>
            {lang[1]}
          </MenuItem>
        ))}
      </Select>
    </StyledFormControl>
  );
};
