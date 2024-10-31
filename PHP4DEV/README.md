DEVs Only!
This is the PHP part to convert and store the images in jOpenSim cache... this includes a basic cache managment that override the existing images or remove the images files on delete.

In this code, we use the things UUID as reference to the images files for eg; the 2nd life profile image name is the avatar UUID, the 1st life... avatarUUID-fli.png... only the picks uses the texture UUID because its use the parcel info image and is possible to many users to create picks for the same parcel... what will create duplicates with different names in the cache.

is also add the type param to getTextureImage function to allow more control over the process in the future... so, you have to add the type everywhere getTextureImage is used... see the getTextureImage switch for the right values...
