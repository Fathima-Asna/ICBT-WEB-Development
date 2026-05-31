FROM maven:3.8.4-openjdk-11-slim AS build
WORKDIR /app
# Adjusted COPY commands to reference the GlobeTrekWeb directory where pom.xml and src exist
COPY GlobeTrekWeb/pom.xml .
COPY GlobeTrekWeb/src ./src
RUN mvn clean package

FROM tomcat:9.0-jdk11-openjdk
COPY --from=build /app/target/*.war /usr/local/tomcat/webapps/ROOT.war
EXPOSE 8080
CMD ["catalina.sh", "run"]
