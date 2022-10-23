import { Box, Typography } from '@mui/material';
import { styled } from '@mui/system';

const StyledErrorMessage = styled(Box)(({ theme }) => ({
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  marginBottom: theme.spacing(2),
}));

type props = {
  message: string | null;
};

export const FormSubmitError = ({ message }: props) => {
  return (
    <StyledErrorMessage>
      <Typography variant='caption' color='error.main'>
        {message}
      </Typography>
    </StyledErrorMessage>
  );
};
