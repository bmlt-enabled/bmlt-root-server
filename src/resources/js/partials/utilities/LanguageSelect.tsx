import { FormControl, InputLabel, MenuItem, Select, SelectChangeEvent } from '@mui/material';
import { useContext } from 'react';

import { ActionType, AppContext } from '../../AppContext';

export const LanguageSelect = () => {
  const languageArr = Object.entries(languageMapping);
  const { state, dispatch } = useContext(AppContext);
  // const [selectedLanguage, setSelectedLanguage] = useState(state.language);

  const handleLanguageChange = (e: SelectChangeEvent) => {
    // setSelectedLanguage(event.target.value as string);
    dispatch({ type: ActionType.SET_LANGUAGE, payload: e.target.value });
  };
  return (
    <FormControl>
      <InputLabel id='language-select-label'>Language</InputLabel>
      <Select
        labelId='language-select-label'
        variant='filled'
        autoWidth
        id='language-select'
        value={state.language}
        label='Language'
        onChange={handleLanguageChange}
      >
        {languageArr.map((lang) => (
          <MenuItem key={lang[0]} value={lang[0]}>
            {lang[1]}
          </MenuItem>
        ))}
      </Select>
    </FormControl>
  );
};
