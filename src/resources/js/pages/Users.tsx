import { Box, Button, FormControl, FormHelperText, InputLabel, MenuItem, TextField, Typography } from '@mui/material';
import Select, { SelectChangeEvent } from '@mui/material/Select';
import { styled } from '@mui/system';
import { User } from 'bmlt-root-server-client';
import { useEffect, useState } from 'react';
import { useForm } from 'react-hook-form';

import RootServerApi from '../RootServerApi';
import { strings } from '../localization';

export const Users = () => {
  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm();
  const [currentSelection, setCurrentSelection] = useState(-1);
  const [selectedUser, setSelectedUser] = useState<User | null>(null);
  const [users, setUsers] = useState<User[]>([]);
  const [getUsersErrorMessage, setGetUsersErrorMessage] = useState('');
  const [validationMessage, setValidationMessage] = useState({
    username: '',
    name: '',
    password: '',
    type: '',
  });

  const StyledButtonWrapper = styled(Box)(({ theme }) => ({
    display: 'flex',
    justifyContent: 'center',
    marginTop: theme.spacing(2),
  }));

  const StyledInputWrapper = styled(FormControl)(({ theme }) => ({
    marginBottom: theme.spacing(4),
    display: 'flex',
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
  }));

  const StyledFormWrapper = styled(Box)(({ theme }) => ({
    width: '100%',
    padding: '20px',
    border: '1px solid #ccc',
    borderRadius: theme.shape.borderRadius,
    marginTop: '20px',
  }));

  const StyledFormLabel = styled(Typography)(({ theme }) => ({
    marginBottom: theme.spacing(2),
    color: theme.palette.primary.main,
  }));

  const handleUserChange = (event: SelectChangeEvent) => {
    setCurrentSelection(parseInt(event.target.value));

    users.forEach((user) => {
      if (parseInt(event.target.value) === user.id) {
        setSelectedUser(user);
      } else if (parseInt(event.target.value) === -1) {
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
        handleError: (error) => setGetUsersErrorMessage(error.message),
      });
    }
  };

  const applyChanges = () => {
    console.log('Changes have been applied');
  };

  useEffect(() => {
    getUsers();
  }, []);

  if (getUsersErrorMessage) {
    // make popup error or something like that
    console.log(getUsersErrorMessage);
  }

  return (
    <div>
      <Box>
        <FormControl>
          <InputLabel id='user-select-label'>{strings.userTitle}</InputLabel>
          <Select
            labelId='user-select-label'
            id='user-select'
            value={currentSelection.toString()}
            label={strings.userTitle}
            onChange={handleUserChange}
          >
            <MenuItem value={-1}>{strings.createNewUserTitle}</MenuItem>
            {users.map((currentUser, i) => {
              return (
                <MenuItem value={currentUser.id} key={i}>
                  {currentUser.displayName}
                </MenuItem>
              );
            })}
          </Select>
        </FormControl>
      </Box>
      <StyledFormWrapper>
        <StyledFormLabel variant='h3' align='center'>
          {`${strings.userTitle} ${strings.idTitle} #${selectedUser?.id}`}
        </StyledFormLabel>
        <form onSubmit={handleSubmit(applyChanges)} noValidate>
          <StyledInputWrapper>
            <h3>{strings.userIsATitle}</h3>
            <FormControl
              error={errors?.type?.type === 'required' || validationMessage?.type !== ''}
              {...register('type', { required: true })}
              fullWidth
            >
              <Select labelId='type-select-label' id='type-select' value={selectedUser?.type || ''}>
                <MenuItem value='admin'>Admin</MenuItem>
                <MenuItem value='serviceBodyAdmin'>Service Body Administrator</MenuItem>
                <MenuItem value='observer'>Service Body Observer</MenuItem>
                <MenuItem value='disabled'>Disabled User</MenuItem>
              </Select>
            </FormControl>
            <FormHelperText id='type-error-text'>
              {(validationMessage?.type !== '' && validationMessage?.type) || (errors?.type?.type === 'required' && 'Type is required')}
            </FormHelperText>
          </StyledInputWrapper>
          <StyledInputWrapper>
            <h3>{strings.ownedByTitle}</h3>
            <FormControl {...register('ownerId', { required: false })} fullWidth>
              <Select labelId='type-select-label' id='type-select' value={selectedUser?.ownerId || ''}>
                {users.map((currentUser, i) => {
                  return (
                    <MenuItem value={currentUser.id} key={i}>
                      {currentUser.displayName}
                    </MenuItem>
                  );
                })}
              </Select>
            </FormControl>
          </StyledInputWrapper>
          <StyledInputWrapper>
            <h3>{strings.usernameTitle}</h3>
            <TextField
              error={errors?.username?.type === 'required' || validationMessage?.username !== ''}
              id='username'
              value={selectedUser?.username}
              type='text'
              fullWidth
              required
              variant='outlined'
              aria-describedby='username-error-text'
              {...register('username', { required: true })}
            />
            <FormHelperText id='username-error-text'>
              {(validationMessage?.username !== '' && validationMessage?.username) ||
                (errors?.username?.type === 'required' && 'Username is required')}
            </FormHelperText>
          </StyledInputWrapper>
          <StyledInputWrapper>
            <h3>{strings.nameTitle}</h3>
            <TextField
              error={errors?.name?.type === 'required' || validationMessage?.name !== ''}
              id='name'
              value={selectedUser?.displayName}
              type='text'
              fullWidth
              required
              variant='outlined'
              aria-describedby='name-error-text'
              {...register('name', { required: true })}
            />
            <FormHelperText id='name-error-text'>
              {(validationMessage?.name !== '' && validationMessage?.name) || (errors?.name?.type === 'required' && 'Name is required')}
            </FormHelperText>
          </StyledInputWrapper>
          <StyledInputWrapper>
            <h3>{strings.emailTitle?.slice(0, -1)}</h3>
            <TextField
              id='email'
              value={selectedUser?.email}
              type='text'
              fullWidth
              variant='outlined'
              aria-describedby='email-error-text'
              {...register('email', { required: false })}
            />
          </StyledInputWrapper>
          <StyledInputWrapper>
            <h3>{strings.passwordTitle}</h3>
            <TextField
              // TODO: Only make required if creating new user
              id='password'
              value=''
              type='text'
              fullWidth
              required
              variant='outlined'
              aria-describedby='password-error-text'
              {...register('password', { required: true })}
            />
          </StyledInputWrapper>
          <StyledInputWrapper>
            <h3>{strings.descriptionTitle}</h3>
            <TextField
              id='description'
              value={selectedUser?.description}
              type='text'
              fullWidth
              variant='outlined'
              aria-describedby='description-error-text'
              {...register('description', { required: false })}
            />
          </StyledInputWrapper>
          <StyledButtonWrapper sx={{ display: 'flex', justifyContent: 'center', marginTop: '' }}>
            <Button variant='contained' color='primary' type='submit'>
              {strings.applyChangesTitle}
            </Button>
          </StyledButtonWrapper>
        </form>
      </StyledFormWrapper>
    </div>
  );
};
