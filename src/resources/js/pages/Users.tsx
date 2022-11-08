import Box from '@mui/material/Box';
import FormControl from '@mui/material/FormControl';
import InputLabel from '@mui/material/InputLabel';
import MenuItem from '@mui/material/MenuItem';
import Select, { SelectChangeEvent } from '@mui/material/Select';
import { User } from 'bmlt-root-server-client';
import { useEffect, useState } from 'react';

import RootServerApi from '../RootServerApi';
import { strings } from '../localization';

export const Users = () => {
  const [currentSelection, setCurrentSelection] = useState('-1');
  const [selectedUser, setSelectedUser] = useState<any>({});
  const [users, setUsers] = useState<User[]>([]);

  const handleChange = (event: SelectChangeEvent) => {
    setCurrentSelection(event.target.value as string);

    users.forEach((user) => {
      if (event.target.value === user.id.toString()) {
        setSelectedUser(user);
      } else if (event.target.value === '-1') {
        setSelectedUser({});
      }
    });
  };

  const getUsers = async () => {
    try {
      const allUsers = await RootServerApi.getUsers();
      setUsers(allUsers);
      console.log(allUsers);
    } catch (error: any) {
      console.log(error);
    }
  };

  useEffect(() => {
    getUsers();
  }, []);

  return (
    <div>
      <Box>
        <FormControl>
          <InputLabel id='select-label'>{strings.userTitle}</InputLabel>
          <Select labelId='select-label' id='select' value={currentSelection} label='User' onChange={handleChange}>
            <MenuItem value='-1'>Create New User</MenuItem>
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
      <p>ID: {selectedUser.id}</p>
      <p>User Name: {selectedUser.displayName}</p>
      <p>User Login: {selectedUser.username}</p>
      <p>Email: {selectedUser.email}</p>
      <p>User Is A: {selectedUser.type}</p>
      <p>Description: {selectedUser.description}</p>
    </div>
  );
};
