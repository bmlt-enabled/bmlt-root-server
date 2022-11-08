import { Box, FormControl, InputLabel, MenuItem } from '@mui/material';
import Select, { SelectChangeEvent } from '@mui/material/Select';
import { User } from 'bmlt-root-server-client';
import { useEffect, useState } from 'react';

import RootServerApi from '../RootServerApi';
import { strings } from '../localization';

export const Users = () => {
  const [currentSelection, setCurrentSelection] = useState('-1');
  const [selectedUser, setSelectedUser] = useState<User | null>(null);
  const [users, setUsers] = useState<User[]>([]);
  const [errorMessage, setErrorMessage] = useState('');

  const handleChange = (event: SelectChangeEvent) => {
    setCurrentSelection(event.target.value as string);

    users.forEach((user) => {
      if (event.target.value === user.id.toString()) {
        setSelectedUser(user);
      } else if (event.target.value === '-1') {
        setSelectedUser(null);
      }
    });
  };

  const getUsers = async () => {
    try {
      const allUsers = await RootServerApi.getUsers();
      setUsers(allUsers);
      console.log(allUsers);
    } catch (error: any) {
      RootServerApi.handleErrors(error, {
        handleError: (error) => setErrorMessage(error.message),
      });
    }
  };

  useEffect(() => {
    getUsers();
  }, []);

  if (errorMessage) {
    console.log(errorMessage);
  }

  return (
    <div>
      <Box>
        <FormControl>
          <InputLabel id='select-label'>{strings.userTitle}</InputLabel>
          <Select labelId='select-label' id='select' value={currentSelection} label='User' onChange={handleChange}>
            <MenuItem value='-1'>{strings.createNewUserTitle}</MenuItem>
            {users.map((currentUser, i) => {
              return (
                <MenuItem value={currentUser.id.toString()} key={i}>
                  {currentUser.displayName}
                </MenuItem>
              );
            })}
          </Select>
        </FormControl>
      </Box>
      <p>{`${strings.idTitle}: ${selectedUser?.id}`}</p>
      <p>{`${strings.usernameTitle}: ${selectedUser?.displayName}`}</p>
      <p>{`${strings.userTitle} ${strings.loginTitle}: ${selectedUser?.username}`}</p>
      <p>{`${strings.emailTitle} ${selectedUser?.email}`}</p>
      <p>{`${strings.userIsATitle} ${selectedUser?.type}`}</p>
      <p>{`${strings.descriptionTitle}: ${selectedUser?.description}`}</p>
    </div>
  );
};
