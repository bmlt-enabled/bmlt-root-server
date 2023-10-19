import { FormControl, InputLabel, Select, SelectChangeEvent } from '@mui/material';
import { styled } from '@mui/system';
import { useContext } from 'react';

import { ActionType, AppContext } from '../../AppContext';
import { strings } from '../../localization';

const StyledFormControl = styled(FormControl)(({ theme }) => ({
  borderRadius: theme.shape.borderRadius * 1,
  minWidth: 180,
  backgroundColor: theme.palette.background.main,
}));

export const LanguageSelect = () => {
  const languageArr = Object.entries(settings.languageMapping);
  const { state, dispatch } = useContext(AppContext);

  const handleLanguageChange = (e: SelectChangeEvent) => {
    dispatch({ type: ActionType.SET_LANGUAGE, payload: e.target.value });
  };

  return (
    <StyledFormControl size='small'>
      <InputLabel htmlFor='language-select'>{strings.languageSelectTitle}</InputLabel>
      <Select
        native
        value={state.language}
        onChange={handleLanguageChange}
        label={strings.languageSelectTitle}
        inputProps={{
          name: 'language-select',
          id: 'language-select',
        }}
      >
        {languageArr.map((lang) => (
          <option key={lang[0]} value={lang[0]}>
            {lang[1]}
          </option>
        ))}
      </Select>
    </StyledFormControl>
  );
};
