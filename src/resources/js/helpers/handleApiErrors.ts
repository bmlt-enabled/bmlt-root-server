import { ResponseError } from 'bmlt-root-server-client';

export const handleApiErrors = async (error: any) => {
  if (error.name === 'ResponseError') {
    const responseError = error as ResponseError;
    if (responseError.response.status === 422) {
      const errors = await responseError.response.json();
      console.log('error status', errors);
      return errors.message;
    }
    if (responseError.response.status === 401) {
      const errors = await responseError.response.json();
      console.log('error status', errors);
      return errors.message;
    }
  }
};
