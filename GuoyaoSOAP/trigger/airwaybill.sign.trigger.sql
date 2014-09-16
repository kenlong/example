DROP TRIGGER signTrack
GO

CREATE TRIGGER signTrack ON [dbo].[AirWayBill] 
FOR UPDATE
AS
IF UPDATE(signin) AND ( SELECT ISNULL(weborder,0) FROM INSERTED ) > 0
BEGIN

    INSERT INTO web_ordertrack(sysdno, orderNo, dno, trackType, trackTime, info, operator)
    SELECT i.dno, w.orderno, i.dno, 4, getdate(), '货物签收', '系统自动'
      FROM INSERTED i INNER JOIN web_order w ON i.weborder = w.id
     WHERE ISNULL(i.weborder,0) <> 0 AND isnull(i.signIn,'') <> '' and not exists ( select * from web_ordertrack t where t.dno = i.dno and tracktype = 4 )

END


