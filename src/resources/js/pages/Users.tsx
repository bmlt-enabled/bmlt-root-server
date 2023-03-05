import { Box, Button, FormControl, FormHelperText, InputLabel, MenuItem, TextField, Typography } from '@mui/material';
import Select, { SelectChangeEvent } from '@mui/material/Select';
import { styled } from '@mui/system';
import { User, UserCreate, UserUpdate } from 'bmlt-root-server-client';
import { useContext, useEffect, useState } from 'react';
import { Controller, useForm } from 'react-hook-form';

import { AppContext } from '../AppContext';
import RootServerApi from '../RootServerApi';
import { strings } from '../localization';

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

export const Users = () => {
  const [users, setUsers] = useState<User[]>([]);
  const [currentSelection, setCurrentSelection] = useState<number>(-1);
  const [selectedUser, setSelectedUser] = useState<User | null>(null);
  const [apiErrorMessage, setApiErrorMessage] = useState<string>('');
  const [apiSuccessMessage, setApiSuccessMessage] = useState<string>('');
  const { state } = useContext(AppContext);
  const [validationMessage, setValidationMessage] = useState<UserCreate>({
    username: '',
    displayName: '',
    password: '',
    type: '',
    email: '',
    ownerId: '',
    description: '',
  });

  const {
    register,
    reset,
    handleSubmit,
    setValue,
    control,
    formState: { errors },
  } = useForm<UserCreate | UserUpdate>();

  const MIN_PASSWORD_LENGTH = 12;

  const isValidEmail = (email: any) =>
    // eslint-disable-next-line
    /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(
      email,
    );

  const isUserNameUnique = (userName: any) => {
    users.forEach((user) => {
      if (userName === user.username) {
        return false;
      }
    });

    return true;
  };

  const handleUserChange = (event: SelectChangeEvent): void => {
    const c = parseInt(event.target.value);
    setCurrentSelection(c);
    if (c === -1) {
      setSelectedUser(null);
      reset();
    } else {
      users.forEach((user) => {
        if (c === user.id) {
          setSelectedUser(user);
          return;
        }
      });
    }
  };

  const deleteUser = async (): Promise<void> => {
    try {
      const nameOfDeletedUser = users.find((u) => u.id == currentSelection);
      await RootServerApi.deleteUser(currentSelection);
      // this action is only available to the server admin, so it's OK to assume that we can set the current selection to -1
      setCurrentSelection(-1);
      const newUsers = users.filter((user) => {
        return user.id != currentSelection;
      });
      setUsers(newUsers);
      reset();
      showSuccessMessage(`User ${nameOfDeletedUser?.displayName} successfully deleted!`);
    } catch (error: any) {
      RootServerApi.handleErrors(error, {
        handleError: (error) => {
          showErrorMessage(`Unable to delete user: ${error.message}`);
        },
      });
    }
  };

  const getUsers = async (): Promise<void> => {
    // Only try to get users from the database if the currently logged in user is an admin or serviceBodyAdmin -- otherwise just leave users as [].
    // This would happen if the currently logged in user is an observer or deactivated.
    if (state.user?.type !== 'admin' && state.user?.type !== 'serviceBodyAdmin') {
      return;
    }
    try {
      const allUsers = await RootServerApi.getUsers();
      allUsers.sort((a, b) => a.displayName.localeCompare(b.displayName));
      // The database stores -1 as the ownerID of any user without an explicit owner.  This gets returned as null in the user object.  The UI,
      // however, thinks of any user without an explicit owner as being owned by the server admin, except for the server admin itself.  Thus
      // the owner in the menu will show up as server admin for users without an explict owner.  (Note that the server admin itself won't ever be in
      // this menu -- it gets filtered out.)  The loop that follows checks for this and fixes up the user objects.  This is only relevant if the
      // currently logged in user is the server admin; otherwise no users owned by the server admin will show up on the list.  The initially selected
      // user is 'Create New User' if the server admin is logged in; and otherwise it's the first user on the list of users.  This list omits the
      // currently logged in user, so there is a check to see if the first user on the list is the currently logged in one.  Ick ....
      if (state.user?.type === 'admin') {
        setCurrentSelection(-1);
        for (const u of allUsers) {
          if (u.type !== 'admin' && u.ownerId === null) {
            u.ownerId = state.user.id.toString();
          }
        }
      } else if (allUsers[0].id != state.user?.id) {
        setCurrentSelection(allUsers[0].id);
        setSelectedUser(allUsers[0]);
      } else if (allUsers.length > 1) {
        setCurrentSelection(allUsers[1].id);
        setSelectedUser(allUsers[1]);
      }
      // if we fall through all the conditions, allUsers.length is 1 and the current user is not the server admin.  In this case we just show a note
      // that there aren't any users that can be edited or created.
      setUsers(allUsers);
    } catch (error: any) {
      RootServerApi.handleErrors(error, {
        handleError: (error) => {
          showErrorMessage(error.message);
        },
      });
    }
  };

  const showErrorMessage = (error: string): void => {
    setApiErrorMessage(error);
  };

  // Function for use in fixing 'owned by' menu.  The argument will be either '' (no selection); or the ID of the current selection,
  // converted to a string.  If the argument is '' and the current user is -1 (denoting 'Create New User') and allUsers have been loaded,
  // then convert the argument to the ID for the server administrator.  Otherwise leave it alone.
  function updateOwner(s: string | undefined): string | undefined {
    if (s === '' && currentSelection === -1) {
      const admin = users.find((u) => u.type === 'admin');
      return admin ? admin.id.toString() : '';
    }
    return s;
  }

  const showSuccessMessage = (message: string): void => {
    setApiSuccessMessage(message);
  };

  const clearValidationMessage = (): void => {
    setValidationMessage({
      username: '',
      displayName: '',
      password: '',
      type: '',
      email: '',
      ownerId: '',
      description: '',
    });
  };

  const applyChangesApiError = async (error: any): Promise<void> => {
    clearValidationMessage();

    await RootServerApi.handleErrors(error, {
      handleError: (error) => {
        showErrorMessage(error.message);
      },
      handleValidationError: (error) =>
        setValidationMessage({
          ...validationMessage,
          username: (error?.errors?.username ?? []).join(' '),
          displayName: (error?.errors?.displayName ?? []).join(' '),
          password: (error?.errors?.password ?? []).join(' '),
          type: (error?.errors?.type ?? []).join(' '),
          email: (error?.errors?.email ?? []).join(' '),
          ownerId: (error?.errors?.ownerId ?? []).join(' '),
          description: (error?.errors?.description ?? []).join(' '),
        }),
    });
  };

  const applyChanges = async (user: UserCreate | UserUpdate): Promise<void> => {
    setApiErrorMessage('');
    setApiSuccessMessage('');
    // If the owner is the server admin, set that field to null before storing it in the database (the UI thinks
    // of the owner as the server admin; the database thinks of the user having no owner).  Note that we aren't
    // going to use the object stored in 'user' variable after this, so it's ok to munge its ownerId field.
    // Hack: mollify the type system by actually setting it to '' -- this still ends up with the correct result.
    if (user.ownerId === undefined || (state.user?.type === 'admin' && user.ownerId == state.user.id.toString())) {
      user.ownerId = '';
    }
    // "Create New User" is selected
    if (currentSelection === -1) {
      try {
        const newUser = await RootServerApi.createUser(user as UserCreate);
        reset();
        // fix up the owner of the new user if necessary
        if (state.user?.type === 'admin' && newUser.ownerId === null) {
          newUser.ownerId = state.user.id.toString();
        }
        setUsers([...users, newUser]);
        showSuccessMessage('User ' + newUser.displayName + ' successfully created!');
      } catch (error: any) {
        applyChangesApiError(error);
      }
    } else {
      if (user?.password?.length === 0) {
        delete user?.password;
      }
      try {
        await RootServerApi.updateUser(currentSelection, user as UserUpdate);
        getUsers();
        showSuccessMessage('User successfully updated!');
        reset();
        setCurrentSelection(-1);
      } catch (error: any) {
        applyChangesApiError(error);
      }
    }
  };

  useEffect(() => {
    getUsers();
  }, []);

  useEffect(() => {
    clearValidationMessage();
    setApiErrorMessage('');
    setApiSuccessMessage('');

    if (selectedUser?.ownerId === null) {
      setValue('ownerId', '');
    } else {
      setValue('ownerId', selectedUser?.ownerId as string);
    }

    setValue('username', selectedUser?.username as string);
    setValue('displayName', selectedUser?.displayName as string);
    setValue('type', selectedUser?.type as string);
    setValue('email', selectedUser?.email as string);
    setValue('description', selectedUser?.description as string);
  }, [selectedUser]);

  if (state.user?.type !== 'admin' && users.length < 2) {
    // No child users for the currently logged in user.  Possible alternative to this design: don't show Users menu link at all.
    return <div>{strings.noUsers}</div>;
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
        </FormControl>
      </Box>
      {apiErrorMessage.length > 0 && <p style={{ color: 'red', textAlign: 'center' }}>{apiErrorMessage}</p>}
      {apiSuccessMessage.length > 0 && <p style={{ color: 'green', textAlign: 'center' }}>{apiSuccessMessage}</p>}
      <StyledFormWrapper>
        {currentSelection !== -1 && (
          <StyledFormLabel variant='h3' align='center'>
            {`${strings.userTitle} ${strings.idTitle} #${selectedUser?.id}`}
          </StyledFormLabel>
        )}
        <form onSubmit={handleSubmit(applyChanges)} noValidate>
          <StyledInputWrapper>
            <h3>{strings.userIsATitle}</h3>
            <FormControl fullWidth>
              <Controller
                name='type'
                control={control}
                defaultValue=''
                render={({ field: { onChange, value } }) => (
                  <Select
                    disabled={state.user?.type !== 'admin'}
                    error={errors?.type?.type === 'required' || validationMessage?.type !== ''}
                    labelId='type-select-label'
                    id='type-select'
                    defaultValue=''
                    required
                    value={value}
                    {...register('type', { required: true })}
                    onChange={onChange}
                  >
                    <MenuItem value='serviceBodyAdmin'>{strings.serviceBodyAdminTitle}</MenuItem>
                    <MenuItem value='observer'>{strings.observerTitle}</MenuItem>
                    <MenuItem value='disabled'>{strings.disabledUserTitle}</MenuItem>
                  </Select>
                )}
              />
            </FormControl>
            <FormHelperText id='type-error-text'>
              {(validationMessage?.type !== '' && validationMessage?.type) || (errors?.type?.type === 'required' && 'Type is required')}
            </FormHelperText>
          </StyledInputWrapper>
          <StyledInputWrapper>
            <h3>{strings.ownedByTitle}</h3>
            <FormControl fullWidth>
              <Controller
                name='ownerId'
                control={control}
                defaultValue=''
                render={({ field: { onChange, value } }) => (
                  <Select
                    disabled={state.user?.type !== 'admin'}
                    error={validationMessage?.ownerId !== ''}
                    labelId='owner-id-label'
                    defaultValue=''
                    id='owner-id-select'
                    value={updateOwner(value)}
                    {...register('ownerId', { required: false })}
                    onChange={onChange}
                  >
                    {users.map((currentUser, i) => {
                      return (
                        <MenuItem value={currentUser.id} key={i}>
                          {currentUser.displayName}
                        </MenuItem>
                      );
                    })}
                  </Select>
                )}
              />
            </FormControl>
            <FormHelperText id='ownerId-error-text'>{validationMessage?.ownerId !== '' && validationMessage?.ownerId}</FormHelperText>
          </StyledInputWrapper>
          <StyledInputWrapper>
            <h3>{strings.usernameTitle}</h3>
            <TextField
              error={errors?.username?.type === 'required' || validationMessage?.username !== ''}
              id='username'
              type='text'
              fullWidth
              required
              variant='outlined'
              aria-describedby='username-error-text'
              {...register('username', { required: true, validate: isUserNameUnique, maxLength: 255 })}
            />
            <FormHelperText id='username-error-text'>
              {(validationMessage?.username !== '' && validationMessage?.username) ||
                (errors?.username?.type === 'maxLength' && 'The username must not be greater than 255 characters.') ||
                (errors?.username?.type === 'validate' && 'The username has already been taken.') ||
                (errors?.username?.type === 'required' && 'Username is required')}
            </FormHelperText>
          </StyledInputWrapper>
          <StyledInputWrapper>
            <h3>{strings.nameTitle}</h3>
            <TextField
              error={errors?.displayName?.type === 'required' || validationMessage?.displayName !== ''}
              id='name'
              type='text'
              fullWidth
              required
              variant='outlined'
              aria-describedby='name-error-text'
              {...register('displayName', { required: true, maxLength: 255 })}
            />
            <FormHelperText id='name-error-text'>
              {(validationMessage?.displayName !== '' && validationMessage?.displayName) ||
                (errors?.displayName?.type === 'maxLength' && 'The display name must not be greater than 255 characters.') ||
                (errors?.displayName?.type === 'required' && 'Name is required')}
            </FormHelperText>
          </StyledInputWrapper>
          <StyledInputWrapper>
            <h3>{strings.emailTitle?.slice(0, -1)}</h3>
            <TextField
              error={validationMessage?.email !== ''}
              id='email'
              type='text'
              fullWidth
              variant='outlined'
              aria-describedby='email-error-text'
              {...register('email', { required: false, validate: isValidEmail })}
            />
            <FormHelperText id='email-error-text'>
              {(validationMessage?.email !== '' && validationMessage?.email) ||
                (errors?.email?.type === 'validate' && 'The email must be a valid email address.')}
            </FormHelperText>
          </StyledInputWrapper>
          <StyledInputWrapper>
            <h3>{strings.passwordTitle}</h3>
            <TextField
              error={currentSelection === -1 && (errors?.password?.type === 'required' || validationMessage?.password !== '')}
              id='password'
              type='password'
              fullWidth
              required={currentSelection === -1 ? true : false}
              variant='outlined'
              aria-describedby='password-error-text'
              {...register('password', { required: currentSelection === -1 ? true : false, minLength: MIN_PASSWORD_LENGTH })}
            />
            <FormHelperText id='password-error-text'>
              {(validationMessage?.password !== '' && validationMessage?.password) ||
                (errors?.password?.type === 'minLength' && `The password must be at least ${MIN_PASSWORD_LENGTH} characters.`) ||
                (errors?.password?.type === 'required' && 'Password is required')}
            </FormHelperText>
          </StyledInputWrapper>
          <StyledInputWrapper>
            <h3>{strings.descriptionTitle}</h3>
            <TextField
              error={validationMessage?.description !== ''}
              id='description'
              type='text'
              fullWidth
              variant='outlined'
              aria-describedby='description-error-text'
              {...register('description', { required: false, maxLength: 1024 })}
            />
            <FormHelperText id='description-error-text'>
              {(validationMessage?.description !== '' && validationMessage?.description) ||
                (errors?.description?.type === 'maxLength' && 'The description must not be greater than 1024 characters.')}
            </FormHelperText>
          </StyledInputWrapper>
          <StyledButtonWrapper sx={{ display: 'flex', justifyContent: 'center', marginTop: '' }}>
            <Button variant='contained' color='primary' type='submit'>
              {strings.applyChangesTitle}
            </Button>
          </StyledButtonWrapper>
        </form>
        {currentSelection !== -1 && state.user?.type == 'admin' && (
          <StyledButtonWrapper sx={{ display: 'flex', justifyContent: 'center', marginTop: '' }}>
            <Button variant='contained' color='primary' onClick={deleteUser}>
              {strings.deleteUserTitle}
            </Button>
          </StyledButtonWrapper>
        )}
      </StyledFormWrapper>
    </div>
  );
};
