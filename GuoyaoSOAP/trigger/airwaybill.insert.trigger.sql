DROP TRIGGER addTrack
GO

CREATE TRIGGER addTrack ON [dbo].[AirWayBill] 
FOR INSERT
AS

IF ( SELECT ISNULL(weborder,0) FROM INSERTED ) > 0
BEGIN

    INSERT INTO web_ordertrack(sysdno, orderNo, dno, trackType, trackTime, info, operator)
    SELECT i.dno, w.orderno, i.dno, 1, getdate(), '收货开单', '系统自动'
      FROM INSERTED i INNER JOIN web_order w ON i.weborder = w.id
     WHERE ISNULL(i.weborder,0) <> 0

END

