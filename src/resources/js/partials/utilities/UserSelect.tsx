import { InputLabel, MenuItem, Select } from '@mui/material';
import { useContext } from 'react';

import { AppContext } from '../../AppContext';
import { strings } from '../../localization';
import FormControlWrapper from '../forms/FormControlWrapper';

type Props = {
  handleUserChange: (data: any) => void;
  currentSelection: number;
  users: any[];
  notFoundMessage?: string;
};

export const UserSelect = ({ currentSelection, handleUserChange, users }: Props) => {
  const { state } = useContext(AppContext);
  return (
    <FormControlWrapper>
      <InputLabel id='user-select-label'>{strings.userTitle}</InputLabel>
      <Select
        labelId='user-select-label'
        id='user-select'
        value={currentSelection.toString()}
        label={strings.userTitle}
        onChange={handleUserChange}
      >
        {state.user?.type === 'admin' && <MenuItem value={-1}>{strings.createNewUserTitle}</MenuItem>}
        {users
          .filter((u) => u.id !== state.user?.id)
          .map((currentUser, i) => {
            return (
              <MenuItem value={currentUser.id} key={i}>
                {currentUser.displayName}
              </MenuItem>
            );
          })}
      </Select>
    </FormControlWrapper>
  );
};
