import { Box, Typography } from '@mui/material';
import { styled } from '@mui/system';

const StyledErrorMessage = styled(Box)(({ theme }) => ({
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  marginBottom: theme.spacing(2),
}));

type Props = {
  message: string | null;
};

export const FormSubmitError = ({ message }: Props) => {
  return (
    <StyledErrorMessage>
      <Typography variant='caption' color='error.main'>
        {message}
      </Typography>
    </StyledErrorMessage>
  );
};
