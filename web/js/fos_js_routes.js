fos.Router.setData({"base_url":"","routes":{"ksUser_show":{"tokens":[["text","\/show"],["variable","\/","[^\/]+?","id"]],"defaults":[],"requirements":[],"hosttokens":[]},"ks_user_public_profile":{"tokens":[["variable","\/","[^\/]+?","username"],["text","\/public_profile"]],"defaults":[],"requirements":[],"hosttokens":[]},"ks_user_import_runkeeper":{"tokens":[["text","\/import_runkeeper"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksUser_activeService":{"tokens":[["variable","_","[^_]+?","serviceId"],["variable","\/","[^\/_]+?","userId"],["text","\/activeService"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksUser_deactiveService":{"tokens":[["variable","_","[^_]+?","serviceId"],["variable","\/","[^\/_]+?","userId"],["text","\/deactiveService"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksUser_postFeedback":{"tokens":[["text","\/postFeedback"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksUser_updateGodFather":{"tokens":[["text","\/updateGodFather"]],"defaults":[],"requirements":[],"hosttokens":[]},"service_authWithRunkeeper":{"tokens":[["text","\/service\/authWithRunkeeper"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksyncRunkeeper_createJob":{"tokens":[["text","\/service\/RunkeepercreateJob"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksyncEndomondo_createJob":{"tokens":[["text","\/service\/EndomondocreateJob"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksRevokeServiceToken":{"tokens":[["text","\/service\/ksRevokeServiceToken"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksFriends_loadFbAnbGoogleContacts":{"tokens":[["text","\/friends\/loadFbAnbGoogleContacts"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksFriends_loadFbFriends":{"tokens":[["text","\/friends\/loadFbFriends"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksFriends_loadGoogleContacts":{"tokens":[["text","\/friends\/loadGoogleContacts"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksFriends_sendFriendRequests":{"tokens":[["text","\/friends\/sendFriendRequests"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksFriends_sendMailInvitations":{"tokens":[["text","\/friends\/sendMailInvitations"]],"defaults":[],"requirements":[],"hosttokens":[]},"ks_user_Ask_a_friend":{"tokens":[["variable","\/","[^\/]+?","user2Id"],["text","\/friends\/ask_a_friend"]],"defaults":[],"requirements":[],"hosttokens":[]},"ks_user_Revoke_a_friend":{"tokens":[["variable","\/","[^\/]+?","user2Id"],["text","\/friends\/revoke_a_friend"]],"defaults":[],"requirements":[],"hosttokens":[]},"ks_user_Cancel_the_friend_request":{"tokens":[["variable","\/","[^\/]+?","user2Id"],["text","\/friends\/cancel_a_friend_request"]],"defaults":[],"requirements":[],"hosttokens":[]},"ks_user_Accept_a_friend_request":{"tokens":[["variable","\/","[^\/]+?","user2Id"],["text","\/friends\/accept_a_friend_request"]],"defaults":[],"requirements":[],"hosttokens":[]},"ks_user_Refuse_a_friend_request":{"tokens":[["variable","\/","[^\/]+?","user2Id"],["text","\/friends\/refuse_a_friend_request"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksTranslation_loadDatatables_translations":{"tokens":[["text","\/translations\/loadDatatables_translations"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksTranslation_loadFullCalendar_translations":{"tokens":[["text","\/translations\/loadFullCalendar_translations"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksNotification_read":{"tokens":[["text","\/notifications\/notifs\/read"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksNotification_validateActivity":{"tokens":[["variable","_","[^_]+?","notificationId"],["variable","\/","[^\/_]+?","activityId"],["text","\/notifications\/validateActivity"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksNotification_unvalidateActivity":{"tokens":[["variable","_","[^_]+?","notificationId"],["variable","\/","[^\/_]+?","activityId"],["text","\/notifications\/unvalidateActivity"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksClub_show":{"tokens":[["text","\/show"],["variable","\/","[^\/]+?","id"],["text","\/clubs"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksClub_getTeamDetailsBloc":{"tokens":[["variable","\/","[^\/]+?","teamId"],["text","\/clubs\/getTeamDetailsBloc"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksClub_askForMembership":{"tokens":[["variable","\/","[^\/]+?","clubId"],["text","\/clubs\/askForMembership"]],"defaults":[],"requirements":{"activityId":"\\d+"},"hosttokens":[]},"ksClub_removeAskForMembership":{"tokens":[["variable","\/","[^\/]+?","clubId"],["text","\/clubs\/removeAskForMembership"]],"defaults":[],"requirements":{"activityId":"\\d+"},"hosttokens":[]},"ksClub_sendInviteByMail":{"tokens":[["variable","\/","\\d+","clubId"],["text","\/clubs\/sendInviteByMail"]],"defaults":[],"requirements":{"clubId":"\\d+"},"hosttokens":[]},"ksClub_sendInviteByNotif":{"tokens":[["variable","\/","\\d+","clubId"],["text","\/clubs\/sendInviteByNotif"]],"defaults":[],"requirements":{"clubId":"\\d+"},"hosttokens":[]},"ksProfileClub_changeAvatar":{"tokens":[["variable","\/","[^\/]+?","clubId"],["text","\/clubs\/profile\/changeAvatar"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksClub_loadClubActivities":{"tokens":[["variable","_","\\d+","offset"],["variable","\/","\\d+","clubId"],["text","\/clubs\/activities\/loadClubActivities"]],"defaults":[],"requirements":{"clubId":"\\d+","offset":"\\d+"},"hosttokens":[]},"ksClub_publishStatus":{"tokens":[["variable","\/","\\d+","clubId"],["text","\/clubs\/activities\/publishStatus"]],"defaults":[],"requirements":{"clubId":"\\d+"},"hosttokens":[]},"ksClub_publishLink":{"tokens":[["variable","\/","\\d+","clubId"],["text","\/clubs\/activities\/publishLink"]],"defaults":[],"requirements":{"clubId":"\\d+"},"hosttokens":[]},"ksClub_publishAlbumPhoto":{"tokens":[["variable","\/","\\d+","clubId"],["text","\/clubs\/activities\/publishAlbumPhoto"]],"defaults":[],"requirements":{"clubId":"\\d+"},"hosttokens":[]},"ksClubAdmin_addClubUser":{"tokens":[["variable","\/","\\d+","clubId"],["text","\/clubs\/clubAdmin\/addClubUser"]],"defaults":[],"requirements":{"clubId":"\\d+"},"hosttokens":[]},"ksClubAdmin_acceptAnAskForMembershipInProgress":{"tokens":[["variable","_","[^_]+?","userId"],["variable","\/","\\d+","clubId"],["text","\/clubs\/clubAdmin\/acceptAnAskForMembershipInProgress"]],"defaults":[],"requirements":{"clubId":"\\d+"},"hosttokens":[]},"ksClubAdmin_deleteClubUser":{"tokens":[["variable","_","[^_]+?","userId"],["variable","\/","\\d+","clubId"],["text","\/clubs\/clubAdmin\/deleteClubUser"]],"defaults":[],"requirements":{"clubId":"\\d+"},"hosttokens":[]},"ksClubAdmin_refuseAnAskForMembershipInProgress":{"tokens":[["variable","_","[^_]+?","userId"],["variable","\/","\\d+","clubId"],["text","\/clubs\/clubAdmin\/refuseAnAskForMembershipInProgress"]],"defaults":[],"requirements":{"clubId":"\\d+"},"hosttokens":[]},"ksClubAdmin_createTournament":{"tokens":[["variable","\/","[^\/]+?","nbParticipants"],["text","\/createTournament"],["variable","\/","[^\/]+?","clubId"],["text","\/clubs\/clubAdmin"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksEvent_show":{"tokens":[["text","\/show"],["variable","\/","[^\/]+?","id"],["text","\/events"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksEvent_getEventEditForm":{"tokens":[["variable","\/","\\d+","eventId"],["text","\/events\/getEventEditForm"]],"defaults":[],"requirements":{"eventId":"\\d+"},"hosttokens":[]},"ksEvent_editEvent":{"tokens":[["variable","\/","\\d+","eventId"],["text","\/events\/editEvent"]],"defaults":[],"requirements":{"eventId":"\\d+"},"hosttokens":[]},"ksEvent_userParticipation":{"tokens":[["variable","\/","\\d+","eventId"],["text","\/events\/userParticipation"]],"defaults":[],"requirements":{"eventId":"\\d+"},"hosttokens":[]},"ksEvent_removeUserParticipation":{"tokens":[["variable","\/","\\d+","eventId"],["text","\/events\/removeUserParticipation"]],"defaults":[],"requirements":{"eventId":"\\d+"},"hosttokens":[]},"ksEvent_eventInfos":{"tokens":[["text","\/eventInfos"],["variable","\/","[^\/]+?","id"],["text","\/events"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksEvent_eventForm":{"tokens":[["text","\/eventForm"],["variable","\/","[^\/]+?","id"],["text","\/events"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksEventClub_show":{"tokens":[["text","\/show"],["variable","\/","[^\/]+?","id"],["text","\/clubs_events"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksProfile_changeAvatar":{"tokens":[["text","\/profile\/changeAvatar"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksActivity_activitiesList":{"tokens":[["text","\/activities\/newsFeed"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksActivity_loadActivities":{"tokens":[["variable","\/","\\d+","offset"],["text","\/activities\/load"]],"defaults":[],"requirements":{"offset":"\\d+"},"hosttokens":[]},"ksActivity_loadSportSessions":{"tokens":[["variable","\/","\\d+","offset"],["variable","\/","[^\/]+?","sportId"],["text","\/activities\/loadSportSessions"]],"defaults":[],"requirements":{"offset":"\\d+"},"hosttokens":[]},"ksActivity_getNotDisplayedLastActivities":{"tokens":[["text","\/activities\/notDisplayedActivities"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksActivity_voteOnActivity":{"tokens":[["variable","\/","\\d+","activityId"],["text","\/activities\/voteOnActivity"]],"defaults":[],"requirements":{"activityId":"\\d+"},"hosttokens":[]},"ksActivity_removeVoteOnActivity":{"tokens":[["variable","\/","\\d+","activityId"],["text","\/activities\/removeVoteOnActivity"]],"defaults":[],"requirements":{"activityId":"\\d+"},"hosttokens":[]},"ksActivity_subscribeOnActivity":{"tokens":[["variable","\/","\\d+","activityId"],["text","\/activities\/subscribeOnActivity"]],"defaults":[],"requirements":{"activityId":"\\d+"},"hosttokens":[]},"ksActivity_unsubscribeOnActivity":{"tokens":[["variable","\/","\\d+","activityId"],["text","\/activities\/unsubscribeOnActivity"]],"defaults":[],"requirements":{"activityId":"\\d+"},"hosttokens":[]},"ksActivity_changeStateOfHealthOnActivity":{"tokens":[["variable","_","[^_]+?","stateOfHealthId"],["variable","\/","\\d+","activityId"],["text","\/activities\/changeStateOfHealthOnActivity"]],"defaults":[],"requirements":{"activityId":"\\d+"},"hosttokens":[]},"ksActivity_loadActivityToBeShared":{"tokens":[["variable","\/","\\d+","activityId"],["text","\/activities\/loadActivityToBeShared"]],"defaults":[],"requirements":{"activityId":"\\d+"},"hosttokens":[]},"ksActivity_shareActivity":{"tokens":[["variable","\/","\\d+","activityId"],["text","\/activities\/shareActivity"]],"defaults":[],"requirements":{"activityId":"\\d+"},"hosttokens":[]},"ksActivity_loadActivityStatus":{"tokens":[["text","\/activities\/loadActivityStatus\/"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksActivity_publishStatus":{"tokens":[["text","\/activities\/publishStatus"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksActivity_updateStatus":{"tokens":[["variable","\/","[^\/]+?","activityId"],["text","\/activities\/updateStatus"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksActivity_publishActivitySession":{"tokens":[["variable","\/","\\d+","clubId"],["variable","\/","\\d+","sportId"],["text","\/activities\/publishActivitySession"]],"defaults":[],"requirements":{"sportId":"\\d+","clubId":"\\d+"},"hosttokens":[]},"ksActivity_updateActivitySession":{"tokens":[["variable","\/","[^\/]+?","activityId"],["text","\/activities\/updateActivitySession"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksActivity_publishTeamSportSession":{"tokens":[["variable","\/","\\d+","clubId"],["variable","\/","\\d+","sportId"],["text","\/activities\/publishTeamSportSession"]],"defaults":[],"requirements":{"sportId":"\\d+","clubId":"\\d+"},"hosttokens":[]},"ksActivity_updateTeamSportSession":{"tokens":[["variable","\/","[^\/]+?","activityId"],["text","\/activities\/updateTeamSportSession"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksActivity_publishEnduranceSession":{"tokens":[["variable","\/","\\d+","clubId"],["variable","\/","\\d+","sportId"],["text","\/activities\/publishEnduranceSession"]],"defaults":[],"requirements":{"sportId":"\\d+","clubId":"\\d+"},"hosttokens":[]},"ksActivity_updateEnduranceSession":{"tokens":[["variable","\/","[^\/]+?","activityId"],["text","\/activities\/updateEnduranceSession"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksActivity_publishLink":{"tokens":[["text","\/activities\/publishLink"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksActivity_publishAlbumPhoto":{"tokens":[["text","\/activities\/publishAlbumPhoto"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksActivity_disableActivity":{"tokens":[["variable","\/","\\d+","activityId"],["text","\/activities\/disableActivity"]],"defaults":[],"requirements":{"activityId":"\\d+"},"hosttokens":[]},"ksActivity_deleteActivity":{"tokens":[["variable","\/","\\d+","activityId"],["text","\/activities\/deleteActivity"]],"defaults":[],"requirements":{"activityId":"\\d+"},"hosttokens":[]},"ksActivity_hideActivity":{"tokens":[["variable","\/","\\d+","activityId"],["text","\/activities\/hideActivity"]],"defaults":[],"requirements":{"activityId":"\\d+"},"hosttokens":[]},"ksActivity_warnActivityLikeDisturbing":{"tokens":[["variable","\/","\\d+","activityId"],["text","\/activities\/warnActivityLikeDisturbing"]],"defaults":[],"requirements":{"activityId":"\\d+"},"hosttokens":[]},"ksActivity_removeWarnActivityLikeDisturbing":{"tokens":[["variable","\/","\\d+","activityId"],["text","\/activities\/removeWarnActivityLikeDisturbing"]],"defaults":[],"requirements":{"activityId":"\\d+"},"hosttokens":[]},"ksActivity_parseLink":{"tokens":[["text","\/activities\/parseLink"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksActivity_imagesUpload":{"tokens":[["variable","\/","[^\/]+?","uploadDirName"],["text","\/activities\/imagesUpload"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksActivity_shareEmailActivity":{"tokens":[["variable","\/","\\d+","activityId"],["text","\/activities\/shareActivityEmail"]],"defaults":[],"requirements":{"activityId":"\\d+"},"hosttokens":[]},"ksActivity_getActivitySessionList":{"tokens":[["text","\/activities\/getActivitySessionList"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksActivity_connectToNewEvent":{"tokens":[["variable","\/","\\d+","eventTypeId"],["text","\/activities\/connectToNewEvent"]],"defaults":[],"requirements":{"eventTypeId":"\\d+"},"hosttokens":[]},"ksActivity_connectToExistantEvent":{"tokens":[["variable","_","\\d+","eventId"],["variable","\/","\\d+","activityId"],["text","\/activities\/connectToExistantEvent"]],"defaults":[],"requirements":{"eventId":"\\d+","activityId":"\\d+"},"hosttokens":[]},"ksActivity_getDataGraph":{"tokens":[["variable","\/","[^\/]+?","activityId"],["text","\/activities\/getDataGraph"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksActivity_readImportantStatus":{"tokens":[["variable","\/","\\d+","activityId"],["text","\/activities\/readImportantStatus"]],"defaults":[],"requirements":{"activityId":"\\d+"},"hosttokens":[]},"ksActivity_activitiesByParameters":{"tokens":[["text","\/activities\/activitiesByParameters"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksActivity_checkSynchronisationInProgress":{"tokens":[["text","\/activities\/checkSynchronisationInProgress"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksActivity_garminImport":{"tokens":[["text","\/garmin\/import"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksActivity_publishComment":{"tokens":[["variable","\/","[^\/]+?","activityId"],["text","\/comments\/publishComment"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksActivity_getCommentForm":{"tokens":[["variable","\/","[^\/]+?","activityId"],["text","\/comments\/getCommentForm"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksActivity_getNotDisplayedLastComments":{"tokens":[["text","\/comments\/notDisplayedComments"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksActivity_getSportSessionForm":{"tokens":[["variable","\/","\\d+","tournamentId"],["variable","\/","\\d+","clubId"],["variable","\/","\\d+","sportId"],["text","\/sports\/getSportSessionForm"]],"defaults":[],"requirements":{"sportId":"\\d+","clubId":"\\d+","tournamentId":"\\d+"},"hosttokens":[]},"ksActivity_getActivitySessionForm":{"tokens":[["variable","\/","[^\/]+?","eventId"],["variable","\/","[^\/]+?","activityId"],["text","\/sports\/getActivitySessionForm"]],"defaults":[],"requirements":{"offset":"\\d+"},"hosttokens":[]},"ksActivity_loadSportChoiceForm":{"tokens":[["text","\/sports\/loadSportChoiceForm\/"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksSport_activitySessionForm":{"tokens":[["variable","\/","[^\/]+?","activityId"],["text","\/sports\/activitySessionForm"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksSport_customSelectSports":{"tokens":[["text","\/sports\/customSelectSports"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksArticle_show":{"tokens":[["text","\/show"],["variable","\/","[^\/]+?","articleId"],["text","\/articles"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksArticle_create":{"tokens":[["variable","\/","[^\/]+?","categoryId"],["text","\/articles\/create"]],"defaults":[],"requirements":{"_method":"post"},"hosttokens":[]},"ksArticle_getContentUpdateForm":{"tokens":[["variable","\/","[^\/]+?","articleId"],["text","\/articles\/getContentUpdateForm"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksArticle_showDifferences":{"tokens":[["variable","_","[^_]+?","modificationId"],["variable","_","[^_]+?","articleId"],["variable","\/","[^\/_]+?","differencesType"],["text","\/articles\/showDifferences"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksArticle_edit":{"tokens":[["text","\/edit"],["variable","\/","[^\/]+?","articleId"],["text","\/articles"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksArticle_addTag":{"tokens":[["text","\/articles\/addTag"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksArticle_anymoreBeingEdited":{"tokens":[["variable","\/","[^\/]+?","articleId"],["text","\/articles\/anymoreBeingEdited"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksArticle_participationInArticleSportingEvent":{"tokens":[["variable","\/","\\d+","articleId"],["text","\/articles\/participationInArticleSportingEvent"]],"defaults":[],"requirements":{"articleId":"\\d+"},"hosttokens":[]},"ksArticle_removeParticipationInArticleSportingEvent":{"tokens":[["variable","\/","\\d+","articleId"],["text","\/articles\/removeParticipationInArticleSportingEvent"]],"defaults":[],"requirements":{"articleId":"\\d+"},"hosttokens":[]},"ksWikisport_show":{"tokens":[["text","\/show"],["variable","\/","[^\/]+?","id"],["text","\/wikisport"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksWikisport_edit":{"tokens":[["text","\/edit"],["variable","\/","[^\/]+?","id"],["text","\/wikisport"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksWikisport_update":{"tokens":[["text","\/update"],["variable","\/","[^\/]+?","id"],["text","\/wikisport"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksActivity_configureNikePlusAccount":{"tokens":[["text","\/nike\/configureNikePlusAccount"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksActivity_syncNikePlusRuns":{"tokens":[["text","\/nike\/syncNikePlusRuns"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksActivity_createNikePlusJob":{"tokens":[["text","\/nike\/createNikePlusJob"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksActivity_configureEndomondoAccount":{"tokens":[["text","\/endomondo\/configureEndomondoAccount"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksSportsmenSearch_list":{"tokens":[["text","\/sportsmen_search\/"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksSportsmenSearch_publishSportsmenSearch":{"tokens":[["text","\/sportsmen_search\/publishSportsmenSearch"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksUser_search":{"tokens":[["text","\/sportingActivities\/searchUsers"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksMessage_box":{"tokens":[["variable","\/","[^\/]+?","numPage"],["text","\/messages\/box"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksMessage_new":{"tokens":[["text","\/messages\/new"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksMessage_send":{"tokens":[["text","\/messages\/sendMessage"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksMessage_delete":{"tokens":[["text","\/delete"],["variable","\/","[^\/]+?","id"],["text","\/messages"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksDashboard_statistics":{"tokens":[["variable","\/","[^\/]+?","id"],["text","\/dashboard"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksDashboard_getDataGraphPointsBySportByMonth":{"tokens":[["variable","\/","[^\/]+?","id"],["text","\/dashboard\/getDataGraphPointsBySportByMonth"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksDashboard_getDataGraphPointsByCommonSportVersusUser":{"tokens":[["text","\/dashboard\/getDataGraphPointsByCommonSportVersusUser\/"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksDashboard_getDataGraphTopLeaguesUsers":{"tokens":[["text","\/dashboard\/getDataGraphTopLeaguesUsers\/"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksDashboard_getDataGraphCumulPointsOnPeriod":{"tokens":[["text","\/dashboard\/getDataGraphCumulPointsOnPeriod\/"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksDashboard_getDataGraphDependingOnSport":{"tokens":[["text","\/dashboard\/getDataGraphDependingOnSport\/"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksAgenda_index":{"tokens":[["variable","\/","[^\/]+?","id"],["text","\/agenda"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksAgenda_getEvents":{"tokens":[["text","\/getEvents"],["variable","\/","[^\/]+?","id"],["text","\/agenda"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksAgenda_createEvent":{"tokens":[["text","\/createEvent"],["variable","\/","[^\/]+?","id"],["text","\/agenda"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksAgenda_editEvent":{"tokens":[["text","\/editEvent"],["variable","\/","[^\/]+?","id"],["text","\/agenda"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksAgenda_deleteEvent":{"tokens":[["text","\/deleteEvent"],["variable","\/","[^\/]+?","id"],["text","\/agenda"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksAgenda_moveOrResizeEvent":{"tokens":[["text","\/moveOrResize"],["variable","\/","[^\/]+?","id"],["text","\/agenda"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksAgenda_getEventInfos":{"tokens":[["text","\/getEventInfos"],["variable","\/","[^\/]+?","id"],["text","\/agenda"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksAgendaClub_index":{"tokens":[["variable","\/","[^\/]+?","id"],["text","\/club_agenda"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksAgendaClub_getEvents":{"tokens":[["text","\/getEvents"],["variable","\/","[^\/]+?","id"],["text","\/club_agenda"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksAgendaClub_createEvent":{"tokens":[["text","\/createEvent"],["variable","\/","[^\/]+?","id"],["text","\/club_agenda"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksContest_coach":{"tokens":[["text","\/contests\/coach"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksTournament_index":{"tokens":[["text","\/tournaments\/"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksTournament_show":{"tokens":[["text","\/show"],["variable","\/","[^\/]+?","id"],["text","\/tournaments"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksTournament_getPodiumForm":{"tokens":[["text","\/getPodiumForm"],["variable","\/","[^\/]+?","id"],["text","\/tournaments"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksTournament_updatePodium":{"tokens":[["text","\/updatePodium"],["variable","\/","[^\/]+?","id"],["text","\/tournaments"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksTournamentMatch_getMatchForm":{"tokens":[["text","\/getMatchForm"],["variable","\/","[^\/]+?","id"],["text","\/tournament_matchs"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksTournamentMatch_updateMatch":{"tokens":[["text","\/updateMatch"],["variable","\/","[^\/]+?","id"],["text","\/tournament_matchs"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksSearch":{"tokens":[["text","\/search\/"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksActivity_getTrophiesInThisCategory":{"tokens":[["variable","_","[^_]+?","trophyCategoryId"],["variable","\/","[^\/_]+?","userId"],["text","\/trophies\/getTrophiesInThisCategory"]],"defaults":[],"requirements":[],"hosttokens":[]},"ksActivity_exposeTrophyInMyShowcase":{"tokens":[["variable","\/","\\d+","trophyId"],["text","\/trophies\/exposeTrophyInMyShowcase"]],"defaults":[],"requirements":{"trophyId":"\\d+"},"hosttokens":[]},"ksActivity_takeOfFromShowcase":{"tokens":[["variable","\/","\\d+","trophyId"],["text","\/trophies\/takeOfFromShowcase"]],"defaults":[],"requirements":{"trophyId":"\\d+"},"hosttokens":[]}},"prefix":"","host":"localhost","scheme":"http"});