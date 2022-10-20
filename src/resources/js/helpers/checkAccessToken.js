import RootServerApi from "../RootServerApi";

export const checkAccessToken = () => {
    const token = localStorage.getItem("accessToken");
    if (token) {
        RootServerApi.accessToken = token;
    }
};
