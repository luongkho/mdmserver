<h1>User infos List</h1>

<table>
  <thead>
    <tr>
      <th>Id</th>
      <th>Username</th>
      <th>Password</th>
      <th>First name</th>
      <th>Last name</th>
      <th>Email</th>
      <th>Role</th>
      <th>Status</th>
      <th>Last login</th>
      <th>Phone number</th>
      <th>Birthday</th>
      <th>Secret</th>
      <th>Token</th>
      <th>Created at</th>
      <th>Updated at</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($user_infos as $user_info): ?>
    <tr>
      <td><a href="<?php echo url_for('mdm/edit?id='.$user_info->getId()) ?>"><?php echo $user_info->getId() ?></a></td>
      <td><?php echo $user_info->getUsername() ?></td>
      <td><?php echo $user_info->getPassword() ?></td>
      <td><?php echo $user_info->getFirstName() ?></td>
      <td><?php echo $user_info->getLastName() ?></td>
      <td><?php echo $user_info->getEmail() ?></td>
      <td><?php echo $user_info->getRoleId() ?></td>
      <td><?php echo $user_info->getStatus() ?></td>
      <td><?php echo $user_info->getLastLogin() ?></td>
      <td><?php echo $user_info->getPhoneNumber() ?></td>
      <td><?php echo $user_info->getBirthday() ?></td>
      <td><?php echo $user_info->getSecret() ?></td>
      <td><?php echo $user_info->getToken() ?></td>
      <td><?php echo $user_info->getCreatedAt() ?></td>
      <td><?php echo $user_info->getUpdatedAt() ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

  <a href="<?php echo url_for('mdm/new') ?>">New</a>
